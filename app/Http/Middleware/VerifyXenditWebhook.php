<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyXenditWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $callbackToken = $request->header('X-CALLBACK-TOKEN');
        $expectedToken = config('xendit.webhook_token');

        if (empty($expectedToken)) {
            return response()->json([
                'error' => 'Webhook token not configured',
            ], 500);
        }

        if (! hash_equals($expectedToken, $callbackToken ?? '')) {
            return response()->json([
                'error' => 'Invalid callback token',
            ], 401);
        }

        return $next($request);
    }
}
