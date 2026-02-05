<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class XenditSettingController extends Controller
{
    public function __construct(protected XenditService $xenditService) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'enabled' => config('xendit.enabled', false),
            'is_production' => config('xendit.is_production', false),
            'has_secret_key' => ! empty(config('xendit.secret_key')),
            'has_webhook_token' => ! empty(config('xendit.webhook_token')),
            'invoice_duration' => config('xendit.invoice.duration', 3600),
            'currency' => config('xendit.invoice.currency', 'IDR'),
            'payment_methods' => config('xendit.payment_methods'),
        ]);
    }

    public function test(Request $request): JsonResponse
    {
        $secretKey = $request->input('secret_key');

        $result = $this->xenditService->testConnection($secretKey);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
