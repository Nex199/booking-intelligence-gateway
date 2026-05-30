<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\IntegrationLog;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMockBookingWebhook implements ShouldQueue
{
    use Queueable;
    
    public $tries = 3;
    public $backoff = [5,10, 20];

    public function __construct(
        public int $logId)
    {
    }

    public function failed(\Throwable $exception){
        IntegrationLog::create([
            'source' => 'telegram',
            'event_type' => 'message.sent',
            'payload' => [
                'original_log_id' => $this->logId
            ],
            'status' => 'permanent_failure',
            'error_message' => $exception->getMessage(),
        ]);
    }

    public function handle(TelegramService $telegramService): void
    {
        $log = IntegrationLog::findOrFail($this->logId);
        $deal = Deal::where('external_id', $log->external_id)->first();
        if($deal){
            $deal->status = $log->payload['deal_status'];
            $deal->save();
            $log->status = 'processed';
            if($deal->status === 'won'){
                $text = "Сделка #$deal->id $deal->title переведена в статус WON! Внешний ID брони: $deal->external_id";
                
                try{
                $response = $telegramService->sendMessage($text);

                IntegrationLog::create([
                    'source' => 'telegram',
                    'status' => $response->successful() ? 'success' : 'failed',
                    'payload' => [
                        'type' => 'outgoing',
                        'text' => $text,
                        'response_body' => $response->json(),
                        'http_code' => $response->status(),
                    ],
                    'event_type' => 'message.sent'
                ]);
                }
                catch(\Throwable $exception){
                    IntegrationLog::create([
                        'source' => 'telegram',
                        'status' => 'failed_attempt',
                        'error_message' => $exception->getMessage(),
                        'payload' => [
                            'type' => 'outgoing',
                            'text' => $text,
                        ],
                        'event_type' => 'message.sent'
                    ]);

                    throw $exception;
                }
            }
        }
        else{
            $log->status = 'failed';
        }
        $log->save();
    }
}
