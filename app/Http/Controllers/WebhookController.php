<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\StoreWebhookLogAction;
use App\Http\Requests\WebhookRequest;
use App\Jobs\ProcessIncomingLeadJob;
use App\Jobs\ProcessMockBookingWebhook;
use App\Jobs\ProcessWebFormLeadJob;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{
    public function handleMockBooking(WebhookRequest $request, StoreWebhookLogAction $action): JsonResponse
    {
        $data = $request->validated();
        $result = $action->execute($data);
        if ($result === 'duplicate') {
            return response()->json(['status' => $result, 'message' => 'Event already processed'], 200);
        }
        $numb = 2;
        match ($result->event_type) {
            2 => print(2),
            'lead.created' => ProcessIncomingLeadJob::dispatch($result->id),
            'lead.created_webform' => ProcessWebFormLeadJob::dispatch($result->id),
            'deal.status_changed' => ProcessMockBookingWebhook::dispatch($result->id),
            default => throw new \InvalidArgumentException("Неизвестный тип события: {$result->event_type}"),
        };

        return response()->json(['status' => 'received'], 200);
    }
}
