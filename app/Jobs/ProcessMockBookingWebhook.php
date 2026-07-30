<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\DealStatusChanged;
use App\Models\Deal;
use App\Models\IntegrationLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Queue\Queueable;
use InvalidArgumentException;

class ProcessMockBookingWebhook implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = [5, 10, 20];

    public function __construct(
        public int $logId) {}

    public function handle(): void
    {
        $log = IntegrationLog::findOrFail($this->logId);

        $deal = Deal::where('external_id', $log->payload['external_id'])->first();
        if (! $deal) {
            throw new ModelNotFoundException('Попытка смены статуса несуществующей сделки, id лога: '.$log->id);
        }

        match ($log->payload['deal_status']) {
            'crm_new' => $deal->status = 'new',
            'crm_won' => $deal->status = 'won',
            'crm_lost' => $deal->status = 'lost',
            'crm_active' => $deal->status = 'active',
            default => throw new InvalidArgumentException('Неподдерживаемый статус, id лога: '.$log->id)
        };
        $deal->save();
        $log->status = 'processed';
        $log->save();
        event(new DealStatusChanged($deal, $log->event_id));
    }
}
