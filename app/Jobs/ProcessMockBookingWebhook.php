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
    
    public function __construct(
        public int $logId)
    {
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
                $response = $telegramService->sendMessage($text);
                if($response->successful()){
                    $status = 'success';
                }
                else{
                    $status = 'failed';
                }
                IntegrationLog::create([
                    'source' => 'telegram',
                    'status' => $status,
                    'payload' => [
                        'type' => 'outgoing',
                        'text' => $text,
                        'response_body' => $response->json(),
                        'http_code' => $response->status(),
                    ],
                    'event_type' => 'message.sent'
                ]);
            }
        }
        else{
            $log->status = 'failed';
        }
        $log->save();
    }
}
