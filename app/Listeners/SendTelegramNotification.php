<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\StoreWebhookLogAction;
use App\Events\DealStatusChanged;
use App\Exceptions\TelegramApiException;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTelegramNotification implements ShouldQueue
{
    public function handle(DealStatusChanged $event, StoreWebhookLogAction $storeWebhookLogAction, TelegramService $telegramService): void
    {
        try {
            $deal = $event->deal;
            if ($deal->status !== 'won') {
                return;
            }

            $text = "Сделка #$deal->id $deal->title переведена в статус WON! Внешний ID брони: $deal->external_id";

            $start = microtime(true);
            $data = $telegramService->sendMessage($text);
            $end = microtime(true);

            $latencyMs = (int) round(($end - $start) * 1000);

            $storeWebhookLogAction->execute([
                'event_id' => 'tg_'.$event->correlationId,
                'source' => 'telegram',
                'event_type' => 'message.sent',
                'latency_ms' => $latencyMs,
                'status' => 'success',
            ]);
        } catch (TelegramApiException $e) {
            $storeWebhookLogAction->execute([
                'event_id' => 'tg_'.$event->correlationId,
                'source' => 'telegram',
                'event_type' => 'message.sent',
                'status' => 'failed_attempt',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
