<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ProcessIncomingLeadJob;
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
            'source' => 'webform',
        ];

        $response = $this->postJson('api/webhooks/mock-booking', $payload, [
            'X-Webhook-Secret' => config('services.mock_service.secret'),
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('integration_logs', [
            'event_id' => '28ac59a0-facf-4343-85aa-65efc41d4ed9',
            'source' => 'webform',
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
}
