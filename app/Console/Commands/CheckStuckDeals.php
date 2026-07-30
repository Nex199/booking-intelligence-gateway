<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Deal;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckStuckDeals extends Command
{
    protected $signature = 'crm:check-stuck-deals';

    protected $description = 'Checking for forgotten transactions';

    public function handle()
    {
        $telegramService = app(TelegramService::class);
        $deals = Deal::where('status', '<>', 'won')
            ->where('updated_at', '<=', now()->subSecond(1))
            ->get();

        if ($deals->isEmpty()) {
            $this->info('Зависших сделок нет');
        } else {
            try {
                $message = "⚠️ Внимание! Найдено зависших сделок {$deals->count()}шт.\n";
                if ($deals->count() > 10) {
                    $message .= 'Самые старые 10 сделок:';
                    $deals = $deals->take(10);
                } else {
                    $message .= 'Сделки:';
                }

                foreach ($deals as $deal) {
                    $message .= "id: $deal->id\n Название: $deal->title\n Статус: $deal->status\n Дата обновления: $deal->updated_at\n \n ";
                }
                $telegramService->sendMessage($message);
            } catch (\Throwable $e) {
                Log::error('Сбой проверки сделок: '.$e->getMessage());
                $this->error('Ошибка отправки отчета в Telegram');
            }
        }
    }
}
