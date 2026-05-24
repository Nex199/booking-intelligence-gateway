<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    protected string $token;
    protected string $chatId;

    public function __construct(){
        $this->token = (string) config('services.telegram.token');
        $this->chatId = (string) config('services.telegram.chat_id');
    }

    public function sendMessage(string $text){
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";
        $response = Http::timeout(3)->withOptions([
            'proxy' => 'http://127.0.0.1:10809'
        ])->post($url, [
            'chat_id' => $this->chatId,
            'text' => $text,
        ]);
        return $response;
    }
}