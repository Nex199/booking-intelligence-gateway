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
        $stuckDealQuery = Deal::query()
            ->whereNotIn('status', ['won', 'lost'])
            ->where('updated_at', '<=', now()->subMinute(15));
        $dealStuckCount = (clone $stuckDealQuery)->count();
        $dealStuckTop = (clone $stuckDealQuery)->oldest('updated_at')->take(10)->get();
        if ($dealStuckCount == 0) {
            $this->info('Зависших сделок нет');
        } else {
            try {
                $message = "⚠️ Внимание! Найдено зависших сделок {$dealStuckCount}шт.\n";
                if ($dealStuckCount > 10) {
                    $message .= 'Самые старые 10 сделок:';
                } else {
                    $message .= 'Все зависшие сделки:';
                }

                foreach ($dealStuckTop as $deal) {
                    $message .= "\nid: $deal->id\nНазвание: $deal->title\nСтатус: $deal->status\nДата обновления: $deal->updated_at\n \n ";
                }
                $telegramService->sendMessage($message);
            } catch (\Throwable $e) {
                Log::error('Сбой проверки сделок: ', ['exception' => $e]);
                $this->error("Ошибка {$e->getMessage()}, в {$e->getFile()} в строке {$e->getLine()}");
            }
        }
    }
}
