<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIpWhiteList
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIp = config('services.mock_service.allowed_ips', []);

        if (in_array('*', $allowedIp) || in_array($request->ip(), $allowedIp, true)) {
            return $next($request);
        } else {
            return response()->json(['error' => 'Forbidden IP'], 403);
        }
    }
}
