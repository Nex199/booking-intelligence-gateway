<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\IntegrationLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMockBookingWebhook implements ShouldQueue
{
    use Queueable;
    /**
     * Create a new job instance.
     */
    public function __construct(public int $logId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = IntegrationLog::findOrFail($this->logId);
        $deal = Deal::where('external_id', $log->external_id)->first();
        if($deal){
            $deal->status = $log->payload['deal_status'];
            $deal->save();
            $log->status = 'processed';
        }
        else{
            $log->status = 'failed';
        }
        $log->save();
    }
}
