<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TelegramApiException;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;

    protected string $chatId;

    public function __construct()
    {
        $this->token = (string) config('services.telegram.token');
        $this->chatId = (string) config('services.telegram.chat_id');
    }

    public function sendMessage(string $text): Response
    {
        try {
            $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

            $response = Http::retry(3, 100)->timeout(3)->withOptions([
                'proxy' => config('services.proxy.poland'),
            ])->post($url, [
                'chat_id' => $this->chatId,
                'text' => $text,
            ])->throw();

            return $response;
        } catch (Exception $e) {
            Log::error('Telegram service error: '.$e->getMessage());
            throw new TelegramApiException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
