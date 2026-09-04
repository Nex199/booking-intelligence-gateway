<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ProcessIncomingLeadJob;
use App\Models\IntegrationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProcessIncomingLeadJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_successfully_processes_incoming_lead_job(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'intent' => 'booking',
                                        'confidenceScore' => 7,
                                        'reason' => 'Клиент выразил намерение забронировать номер на двоих с пожеланием тишины. Даты заезда и количество ночей не указаны, поэтому оценка уверенности снижена.',
                                        'budget' => 15000,
                                        'checkInDate' => '2026-07-30',
                                        'guestPreferences' => 'тихий номер',
                                        'nights' => 3,
                                        'numberOfGuests' => 2,
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $log = IntegrationLog::create([
            'event_id' => '90d5e143-5af0-423f-8e12-be72e4c56614',
            'source' => 'webform',
            'event_type' => 'lead.created',
            'external_id' => 'ext_123',
            'payload' => [
                'event_id' => '90d5e143-5af0-423f-8e12-be72e4c56614',
                'source' => 'webform',
                'event_type' => 'lead.created',
                'external_id' => 'ext_123',
                'message' => 'Здравствуйте, хочу номер на двоих, тихий. Вот моя почта chelick@gmail.com и номер телефона 79991234567',
                'guest_phone' => '79991234567',
                'guest_email' => 'chelick@gmail.com',
            ],
            'status' => 'received',
        ]);

        ProcessIncomingLeadJob::dispatchSync($log->id);

        $this->assertDatabaseHas('deals', [
            'source' => 'webform',
            'external_id' => 'ext_123',
            'guest_phone' => '79991234567',
            'status' => 'new',
            'budget' => 15000,
            'check_in_date' => '2026-07-30',
            'guest_email' => 'chelick@gmail.com',
        ]);

        $this->assertDatabaseHas('integration_logs', [
            'id' => $log->id,
            'status' => 'processed',
        ]);

        $this->assertDatabaseHas('integration_logs', [
            'event_id' => 'ai_'.$log->event_id,
            'source' => 'gemini',
        ]);

        Http::assertSent(function ($x) {
            $body = $x->body();

            return ! str_contains($body, 'chelick@gmail.com')
            && ! str_contains($body, '79991234567')
            && str_contains($body, '[EMAIL_REDACTED]')
            && str_contains($body, '[PHONE_REDACTED]');
        });
    }

    public function test_sets_manual_review_when_confidence_score_is_low()
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'intent' => 'other',
                                        'confidenceScore' => 2,
                                        'reason' => 'ваще сложно',
                                        'budget' => null,
                                        'checkInDate' => null,
                                        'guestPreferences' => null,
                                        'nights' => null,
                                        'numberOfGuests' => null,
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $log = IntegrationLog::create([
            'event_id' => '384678f2-7baa-4a92-928e-5aa45e17ff18',
            'source' => 'webform',
            'event_type' => 'lead.created',
            'external_id' => 'ext_123',
            'payload' => [
                'message' => 'какойто непонятный текст',
                'guest_phone' => '79991234567',
            ],
            'status' => 'received',
        ]);

        ProcessIncomingLeadJob::dispatchSync($log->id);

        $this->assertDatabaseHas('deals', [
            'guest_phone' => '79991234567',
        ]);
    }
}
