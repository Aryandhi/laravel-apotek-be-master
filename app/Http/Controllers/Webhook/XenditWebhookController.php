<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function __construct(protected XenditService $xenditService) {}

    public function handleInvoice(Request $request): JsonResponse
    {
        Log::info('Xendit Invoice Webhook received', [
            'payload' => $request->all(),
        ]);

        $payload = $request->all();

        try {
            $transaction = $this->xenditService->handleWebhook($payload);

            if ($transaction) {
                Log::info('Xendit Invoice Webhook processed', [
                    'transaction_id' => $transaction->id,
                    'external_id' => $transaction->external_id,
                    'status' => $transaction->status->value,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully',
                ]);
            }

            Log::warning('Xendit Invoice Webhook - Transaction not found', [
                'external_id' => $payload['external_id'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Xendit Invoice Webhook error', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
