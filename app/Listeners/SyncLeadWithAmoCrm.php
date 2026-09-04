<?php

declare(strict_types=1);

namespace App\Listeners;

use AmoCRM\Exceptions\AmoCRMApiException;
use App\Actions\StoreWebhookLogAction;
use App\Events\IncomingLeadProcessed;
use App\Services\AmoCrmService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncLeadWithAmoCrm implements ShouldQueue
{
    public $tries = 3;
    public $backoff = [5, 10, 15];

    public function __construct(public StoreWebhookLogAction $storeWebhookLogAction, public AmoCrmService $amoCrmService)
    { }
    public function handle(IncomingLeadProcessed $event): void
    {
        $deal = $event->deal;
        try {
            $amoStart = microtime(true);

            $amoLeadId = $this->amoCrmService->syncLead(
                $deal->amocrm_lead_id ? (int) $deal->amocrm_lead_id : null,
                [
                    'budget' => $deal->budget,
                    'guest_preferences' => $deal->guest_preferences,
                ]
            );

            if ($deal->amocrm_lead_id == null) {
                $deal->amocrm_lead_id = $amoLeadId;
                $deal->save();
            }

            $amoLatencyMs = (int) round((microtime(true) - $amoStart) * 1000);

            $this->storeWebhookLogAction->execute([
                'event_id' => 'amo_'.$event->correlationId,
                'source' => 'amocrm',
                'event_type' => 'lead.updated',
                'latency_ms' => $amoLatencyMs,
                'status' => 'success',
            ]);
        } catch (AmoCRMApiException $e) {
            $this->storeWebhookLogAction->execute([
                'event_id' => 'amo_'.$event->correlationId,
                'source' => 'amocrm',
                'event_type' => 'lead.updated',
                'status' => 'failed_attempt',
                'error_message' => "Заголовок ошибки: {$e->getTitle()}, Описание ошибки: {$e->getDescription()}",
                'external_id' => $deal->external_id,
            ]);

            throw $e;
        }
    }
}
