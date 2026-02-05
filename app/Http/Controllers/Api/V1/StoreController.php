<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function info(Request $request): JsonResponse
    {
        $user = $request->user();
        $store = $user->store;

        if (! $store) {
            // Return default/first store if user has no store assigned
            $store = Store::first();
        }

        if (! $store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $store->id,
                'name' => $store->name,
                'code' => $store->code,
                'address' => $store->address,
                'phone' => $store->phone,
                'email' => $store->email,
                'sia_number' => $store->sia_number,
                'sipa_number' => $store->sipa_number,
                'pharmacist_name' => $store->pharmacist_name,
                'pharmacist_sipa' => $store->pharmacist_sipa,
                'logo' => $store->logo,
                'receipt_footer' => $store->receipt_footer,
            ],
        ]);
    }

    public function settings(Request $request): JsonResponse
    {
        $user = $request->user();
        $storeId = $user->store_id;

        // Get global settings
        $globalSettings = Setting::global()->get()->keyBy('key');

        // Get store-specific settings if user has store
        $storeSettings = $storeId
            ? Setting::where('store_id', $storeId)->get()->keyBy('key')
            : collect();

        // Merge store settings over global settings
        $settings = $globalSettings->merge($storeSettings);

        // Define setting groups for Flutter
        $groups = [
            'general' => ['app_name', 'currency', 'currency_symbol', 'timezone', 'date_format'],
            'pos' => ['tax_rate', 'default_discount', 'allow_negative_stock', 'require_prescription_verification'],
            'receipt' => ['receipt_header', 'receipt_footer', 'show_logo', 'paper_size'],
            'notification' => ['low_stock_threshold', 'expiry_warning_days'],
        ];

        $formattedSettings = [];
        foreach ($groups as $group => $keys) {
            $formattedSettings[$group] = [];
            foreach ($keys as $key) {
                $setting = $settings->get($key);
                $formattedSettings[$group][$key] = $setting?->value ?? $this->getDefaultValue($key);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $formattedSettings,
        ]);
    }

    private function getDefaultValue(string $key): mixed
    {
        return match ($key) {
            'app_name' => 'Apotek POS',
            'currency' => 'IDR',
            'currency_symbol' => 'Rp',
            'timezone' => 'Asia/Jakarta',
            'date_format' => 'd/m/Y',
            'tax_rate' => '0',
            'default_discount' => '0',
            'allow_negative_stock' => 'false',
            'require_prescription_verification' => 'true',
            'receipt_header' => '',
            'receipt_footer' => 'Terima kasih atas kunjungan Anda',
            'show_logo' => 'true',
            'paper_size' => '80mm',
            'low_stock_threshold' => '10',
            'expiry_warning_days' => '30',
            default => null,
        };
    }
}
