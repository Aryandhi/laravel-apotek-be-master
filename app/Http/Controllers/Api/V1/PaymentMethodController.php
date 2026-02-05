<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    public function index(): JsonResponse
    {
        $methods = PaymentMethod::query()
            ->active()
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $methods->map(fn ($method) => [
                'id' => $method->id,
                'name' => $method->name,
                'code' => $method->code,
                'is_cash' => $method->is_cash,
            ]),
        ]);
    }
}
