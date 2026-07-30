<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\LeadAnalysisResultDto;
use App\Exceptions\GeminiApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiService
{
    public function evaluateLead(string $clientMessage): LeadAnalysisResultDto
    {
        try {
            if (trim($clientMessage) === '') {
                throw new \Exception('Сообщение клиента пустое');
            }
            $today = now()->format('Y-m-d');
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key='.config('services.gemini.key');
            $systemInstruction = <<<PROMPT
            You are a strict backend data extractor for a hotel management system. 
            Today's date is: {$today}. Use this date as your sole anchor point for calculating all relative dates mentioned by the client (e.g., "tomorrow", "next Thursday", "in two weeks").

            Your single task is to analyze the provided chat history and extract the booking parameters into the specified JSON schema.

            CRITICAL LOGIC RULES:
            1. Intent: Set to 'booking' if the user wants to book, check availability, or inquire about prices. Set to 'other' if it is spam, a generic greeting, or completely unrelated.
            2. Spam/Greeting Rule: If intent is 'other', you must leave all booking-specific fields (dates, budget, guests) as null.
            3. Confidence Score: Be critical. Assign 9-10 only if the parameters are explicitly clear. Lower the score if you have to make assumptions due to typos or ambiguous phrasing.
            4. Reason: Write a brief summary in Russian justifying your extraction logic and the confidence score.
PROMPT;
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $clientMessage],
                        ],
                    ],
                ],
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemInstruction],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'intent' => [
                                'type' => 'STRING',
                                'enum' => ['booking', 'other'],
                                'description' => 'Strictly "booking" if client wants to book or inquiry price, otherwise "other".',
                            ],
                            'checkInDate' => [
                                'type' => 'STRING',
                                'nullable' => true,
                                'format' => 'date',
                                'description' => 'Clients arrival date. Must be strictly formatted as YYYY-MM-DD or null if unknown.',
                            ],
                            'nights' => [
                                'type' => 'INTEGER',
                                'nullable' => true,
                                'minimum' => 1,
                                'description' => 'Number of nights of the client',
                            ],
                            'numberOfGuests' => [
                                'type' => 'INTEGER',
                                'nullable' => true,
                                'minimum' => 1,
                                'description' => 'Number of guests',
                            ],
                            'budget' => [
                                'type' => 'NUMBER',
                                'nullable' => true,
                                'description' => 'Guests budget',
                            ],
                            'guestPreferences' => [
                                'type' => 'STRING',
                                'nullable' => true,
                                'description' => 'Guests wishes',
                            ],
                            'confidenceScore' => [
                                'type' => 'INTEGER',
                                'maximum' => 10,
                                'minimum' => 1,
                            ],
                            'reason' => [
                                'type' => 'STRING',
                                'description' => 'A brief explanation of the AI logic in Russian, '
                                .'explaining why such parameters and such a confidence'
                                .'assessment were chosen.',
                            ],
                        ],
                        'required' => ['intent', 'confidenceScore', 'reason'],
                    ],
                ],
            ];
            $start = microtime(true);

            $response = Http::retry(3, 100)
            ->timeout(15)
            ->withOptions(['proxy' => config('services.proxy.poland')])
            ->post($url, $payload);

            $end_time = microtime(true);
            $latencyMs = (int) round(($end_time - $start) * 1000);

            if ($response->failed()) {
                throw new \Exception('Gemini вернул ошибку: '.$response->body());
            }

            $data = $response->json();

            if (! isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Неправильная структура ответа API');
            }

            $rawText = $data['candidates'][0]['content']['parts'][0]['text'];
            $text = json_decode($rawText, true);

            if (! isset($text['intent']) || ! isset($text['reason'])) {
                throw new \Exception('Некорректная структура, отсутствуют обязательные поля.');
            }

            $message = LeadAnalysisResultDto::fromArray($text, $latencyMs);

            return $message;
        } catch (\Exception $e) {
            Log::error('Gemini service error: '.$e->getMessage());
            throw new GeminiApiException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
