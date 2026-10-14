<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\StoreWebhookLogAction;
use App\Events\IncomingLeadProcessed;
use App\Exceptions\GeminiApiException;
use App\Models\Deal;
use App\Models\IntegrationLog;
use App\Services\AmoCrmService;
use App\Services\DataAnonymizationService;
use App\Services\GeminiAiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessIncomingLeadJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = [5, 10, 15];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $logId
    ) {}

    public function handle(GeminiAiService $geminiAiService, DataAnonymizationService $dataAnonymizationService, StoreWebhookLogAction $storeWebhookLogAction): void
    {
        $log = IntegrationLog::findOrFail($this->logId);

        $guestPhone = $log->payload['guest_phone'];
        $message = $log->payload['message'];
        $deal = null;
        if ($log->external_id) {
            $deal = Deal::query()
                ->where('external_id', $log->external_id)
                ->whereNotIn('status', ['won', 'lost'])
                ->first();
        }

        if (! $deal) {
            $deal = Deal::query()
                ->where('guest_phone', $guestPhone)
                ->whereNotIn('status', ['lost', 'won'])
                ->first();
        }

        if (! $deal) {
            $deal = new Deal;
            $deal->status = 'new';
            $deal->external_id = $log->external_id ?? null;
            $deal->title = 'Новая бронь';
            $deal->guest_phone = $guestPhone;
            $deal->guest_email = $log->payload['guest_email'] ?? null;
            $deal->guest_name = $log->payload['guest_name'] ?? null;
            $deal->hotel_name = $log->payload['hotel_name'] ?? null;
            $deal->source = $log->payload['source'] ?? null;
            $deal->chat_history = '';
        }

        if ($log->status === 'received') {
            $deal->chat_history = trim($deal->chat_history.PHP_EOL.'[Клиент]: '.$message);
            $deal->save();
            $log->status = 'processing';
            $log->save();
        }

        try {
            $anonymizedHistory = $dataAnonymizationService->anonymize($deal->chat_history);
            $result = $geminiAiService->evaluateLead($anonymizedHistory);
            if ($result->confidenceScore < 6) {
                $deal->status = 'manual_review';
            }

            $deal->budget = $result->budget;
            $deal->check_in_date = $result->checkInDate;
            $deal->number_of_guests = $result->numberOfGuests;
            $deal->guest_preferences = $result->guestPreferences;
            $deal->ai_analysis = $result->toArray();
            $deal->save();

            event(new IncomingLeadProcessed($deal, $log->event_id, $result));

            $storeWebhookLogAction->execute([
                'event_id' => 'ai_'.$log->event_id,
                'source' => 'gemini',
                'event_type' => 'gemini response',
                'latency_ms' => $result->latencyMs,
                'status' => 'success',
            ]);

            $log->status = 'processed';
            $log->save();
        } catch (GeminiApiException $e) {
            $storeWebhookLogAction->execute([
                'event_id' => 'ai_'.$log->event_id,
                'source' => 'gemini',
                'event_type' => 'gemini response',
                'external_id' => $log->external_id,
                'status' => 'failed_attempt',
                'error_message' => $e->getMessage(),
            ]);

            $log->status = 'failed_attempt';
            $log->error_message = $e->getMessage();
            $log->save();
            throw $e;
        } catch (\Throwable $e) {
            $log->status = 'failed_attempt';
            $log->error_message = $e->getMessage();
            $log->save();
            throw $e;
        }
    }
}
