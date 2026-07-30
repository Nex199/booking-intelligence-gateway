<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\IntegrationLog;
use Illuminate\Database\UniqueConstraintViolationException;

class StoreWebhookLogAction
{
    public function execute(array $data)
    {
        try {
            $attributes = [
                'event_id' => $data['event_id'],
                'source' => $data['source'],
                'event_type' => $data['event_type'],
                'external_id' => $data['external_id'] ?? null,
                'payload' => $data,
                'status' => $data['status'] ?? 'received',
                'latency_ms' => $data['latency_ms'] ?? null,
                'error_message' => $data['error_message'] ?? null,
            ];

            return IntegrationLog::create($attributes);
        } catch (UniqueConstraintViolationException) {
            return 'duplicate';
        }

    }
}
