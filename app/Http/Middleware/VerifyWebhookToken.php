<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\IntegrationLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Webhook-Secret');
        if ($token === config('services.mock_service.secret')) {
            return $next($request);
        } else {
            IntegrationLog::create([
                'source' => 'security-guard',
                'status' => 'unauthorized_attempt',
                'event_type' => $request->input('event_type'),
                'external_id' => $request->input('external_id'),
                'payload' => $request->all(),
                'error_message' => 'The wrong secret',
            ]);

            return response()->json(['error' => 'Forbidden'], 403);
        }

    }
}
