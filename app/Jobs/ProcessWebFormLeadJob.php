<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\StoreWebhookLogAction;
use App\Events\IncomingLeadProcessed;
use App\Models\Deal;
use App\Models\IntegrationLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;
use Throwable;

class ProcessWebFormLeadJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [5, 10, 15];

    public function __construct(
        public int $logId
    ) {}

    public function handle(StoreWebhookLogAction $storeWebhookLogAction): void
    {
        try {
            $log = IntegrationLog::findOrFail($this->logId);

            $guestPhone = $log->payload['guest_phone'];

            $deal = Deal::firstOrNew(['external_id' => $log->external_id]);

            $deal->status = 'new';
            $deal->external_id = $log->external_id;
            $deal->title = 'Новая бронь';
            $deal->guest_phone = $guestPhone;
            $deal->guest_email = $log->payload['guest_email'] ?? null;
            $deal->guest_name = $log->payload['guest_name'] ?? null;
            $deal->hotel_name = $log->payload['hotel_name'] ?? null;
            $deal->guest_preferences = $log->payload['guest_preferences'] ?? null;
            $deal->source = 'webform';
            $deal->save();

            event(new IncomingLeadProcessed($deal, $log->event_id));

            $log->status = 'processed';
            $log->save();
        } catch (Throwable $e) {
            $log->status = 'failed_attempt';
            $log->error_message = $e->getMessage();
            $log->save();
            throw $e;
        }
    }
}
