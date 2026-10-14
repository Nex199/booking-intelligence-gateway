<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ProcessIncomingLeadJob;
use App\Jobs\ProcessWebFormLeadJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Queue;
use Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_successfully_accepts_lead_created_webhook(): void
    {
        Queue::fake();

        $payload = [
            'event_type' => 'lead.created',
            'message' => 'Здравствуйте',
            'event_id' => '28ac59a0-facf-4343-85aa-65efc41d4ed9',
            'guest_phone' => '89991234567',
            'external_id' => 'ext_123',
            'source' => 'telegram',
        ];

        $response = $this->postJson('api/webhooks/mock-booking', $payload, [
            'X-Webhook-Secret' => config('services.mock_service.secret'),
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('integration_logs', [
            'event_id' => '28ac59a0-facf-4343-85aa-65efc41d4ed9',
            'source' => 'telegram',
            'event_type' => 'lead.created',
            'status' => 'received',
        ]);

        Queue::assertPushed(ProcessIncomingLeadJob::class);
    }

    public function test_returns_403_when_webhook_secret_is_invalid(): void
    {
        Queue::fake();

        $payload = [
            'event_type' => 'lead.created',
            'message' => 'Здравствуйте',
            'event_id' => '28ac59a0-facf-4343-85aa-65efc41d4ed9',
            'guest_phone' => '89991234567',
            'external_id' => 'ext_123',
            'source' => 'webform',
        ];

        $response = $this->postJson('api/webhooks/mock-booking', $payload, [
            'X-Webhook-Secret' => 'invalid',
        ]);

        $response->assertStatus(403);
    }

    public function test_blocks_requests_when_rate_limit_exceeded(): void
    {
        Queue::fake();
        $data = [
            'event_type' => 'lead.created',
            'message' => 'Здравствуйте',
            'event_id' => (string) \Illuminate\Support\Str::uuid(),
            'guest_phone' => '79991234566',
            'external_id' => '22e2esdsd',
            'source' => 'telegram',
        ];
        $headers = ['X-Webhook-Secret' => config('services.mock_service.secret')];


        for ($i = 1; $i <= 60; $i++) {
            $data['event_id'] = (string) \Illuminate\Support\Str::uuid();
            $response = $this->postJson(
                'api/webhooks/mock-booking',
                $data,
                $headers,
            )->assertStatus(200);
        }

        $response = $this->postJson(
            'api/webhooks/mock-booking',
            $data,
            $headers,
        );
        $response->assertStatus(429);
    }

    public function test_returns_403_when_ip_is_not_whitelisted(): void
    {
        Queue::fake();
        $data = [
            'event_type' => 'lead.created',
            'message' => 'Здравствуйте',
            'event_id' => (string) \Illuminate\Support\Str::uuid(),
            'guest_phone' => '79991234566',
            'external_id' => '22e2esdsd',
            'source' => 'telegram',
        ];

        $headers = ['X-Webhook-Secret' => config('services.mock_service.secret')];

        $response = $this->withServerVariables(['REMOTE_ADDR' => '242.42.242.42'])
            ->postJson(
                'api/webhooks/mock-booking',
                $data,
                $headers,
            )->assertStatus(403)->assertJson(['error' => 'Forbidden IP']);
    }

    public function test_successfully_accepts_webform_webhook() {
        Queue::fake();

        $payload = [
            'event_type' => 'lead.created_webform',
            'message' => 'Здрасте',
            'guest_name' => 'Alex',
            'event_id' => '578b426c-26c9-4e12-8475-5275ffab349e',
            'guest_phone' => '81234567890',
            'external_id' => 'web_2baf6de1-e11d-49bc-b321-784f6370abe7',
            'source' => 'webform',
            'guest_preferences' => 'Хочу шикарный вид',
        ];

        $headers = ['X-Webhook-Secret' => config('services.mock_service.secret')];

        $response = $this->postJson(
            'api/webhooks/mock-booking',
            $payload,
            $headers,
        )->assertStatus(200)->assertJson(['status' => 'received']);

        $this->assertDatabaseHas('integration_logs', [
            'event_type' => 'lead.created_webform',
            'source' => 'webform',
            'event_id' => '578b426c-26c9-4e12-8475-5275ffab349e',
            'external_id' => 'web_2baf6de1-e11d-49bc-b321-784f6370abe7',
            'status' => 'received',
        ]);

        Queue::assertPushed(ProcessWebFormLeadJob::class);
    }
}
