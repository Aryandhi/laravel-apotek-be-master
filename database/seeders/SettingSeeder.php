<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Store;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::first();

        // General Settings
        $generalSettings = [
            ['key' => 'app_name', 'value' => 'Apotek Sehat Farma', 'group' => 'general'],
            ['key' => 'app_version', 'value' => '1.0.0', 'group' => 'general'],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta', 'group' => 'general'],
            ['key' => 'currency', 'value' => 'Rp', 'group' => 'general'],
            ['key' => 'date_format', 'value' => 'd/m/Y', 'group' => 'general'],
            ['key' => 'time_format', 'value' => 'H:i', 'group' => 'general'],
        ];

        // Invoice Settings
        $invoiceSettings = [
            ['key' => 'invoice_prefix', 'value' => 'INV', 'group' => 'invoice'],
            ['key' => 'purchase_prefix', 'value' => 'PO', 'group' => 'invoice'],
            ['key' => 'return_prefix', 'value' => 'RTN', 'group' => 'invoice'],
            ['key' => 'invoice_footer', 'value' => 'Terima kasih atas kunjungan Anda. Semoga lekas sembuh!', 'group' => 'invoice'],
            ['key' => 'print_logo', 'value' => 'true', 'group' => 'invoice'],
            ['key' => 'print_address', 'value' => 'true', 'group' => 'invoice'],
        ];

        // Stock Settings
        $stockSettings = [
            ['key' => 'low_stock_threshold', 'value' => '10', 'group' => 'stock'],
            ['key' => 'near_expired_days', 'value' => '90', 'group' => 'stock'],
            ['key' => 'enable_batch_tracking', 'value' => 'true', 'group' => 'stock'],
            ['key' => 'enable_expired_tracking', 'value' => 'true', 'group' => 'stock'],
            ['key' => 'auto_deduct_stock', 'value' => 'true', 'group' => 'stock'],
            ['key' => 'fifo_method', 'value' => 'fefo', 'group' => 'stock'], // FEFO = First Expired First Out
        ];

        // Tax Settings
        $taxSettings = [
            ['key' => 'enable_tax', 'value' => 'false', 'group' => 'tax'],
            ['key' => 'tax_rate', 'value' => '11', 'group' => 'tax'],
            ['key' => 'tax_name', 'value' => 'PPN', 'group' => 'tax'],
            ['key' => 'tax_included', 'value' => 'true', 'group' => 'tax'],
        ];

        // Discount Settings
        $discountSettings = [
            ['key' => 'enable_member_discount', 'value' => 'true', 'group' => 'discount'],
            ['key' => 'member_discount_rate', 'value' => '10', 'group' => 'discount'],
            ['key' => 'enable_points', 'value' => 'true', 'group' => 'discount'],
            ['key' => 'points_per_transaction', 'value' => '1', 'group' => 'discount'],
            ['key' => 'points_min_transaction', 'value' => '50000', 'group' => 'discount'],
            ['key' => 'points_to_rupiah', 'value' => '100', 'group' => 'discount'],
        ];

        // Prescription Settings
        $prescriptionSettings = [
            ['key' => 'require_doctor_for_prescription', 'value' => 'true', 'group' => 'prescription'],
            ['key' => 'require_patient_info', 'value' => 'true', 'group' => 'prescription'],
            ['key' => 'prescription_validity_days', 'value' => '3', 'group' => 'prescription'],
            ['key' => 'enable_compounding', 'value' => 'true', 'group' => 'prescription'],
        ];

        // Notification Settings
        $notificationSettings = [
            ['key' => 'notify_low_stock', 'value' => 'true', 'group' => 'notification'],
            ['key' => 'notify_near_expired', 'value' => 'true', 'group' => 'notification'],
            ['key' => 'notify_expired', 'value' => 'true', 'group' => 'notification'],
            ['key' => 'email_notifications', 'value' => 'false', 'group' => 'notification'],
        ];

        $allSettings = array_merge(
            $generalSettings,
            $invoiceSettings,
            $stockSettings,
            $taxSettings,
            $discountSettings,
            $prescriptionSettings,
            $notificationSettings
        );

        foreach ($allSettings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key'], 'store_id' => null],
                [
                    'key' => $setting['key'],
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'store_id' => null,
                ]
            );
        }

        // Store-specific settings
        if ($store) {
            $storeSettings = [
                ['key' => 'receipt_header', 'value' => 'APOTEK SEHAT FARMA', 'group' => 'receipt'],
                ['key' => 'receipt_subheader', 'value' => 'Melayani dengan Sepenuh Hati', 'group' => 'receipt'],
                ['key' => 'receipt_footer', 'value' => 'Terima kasih - Semoga lekas sembuh!', 'group' => 'receipt'],
                ['key' => 'opening_cash', 'value' => '500000', 'group' => 'cashier'],
            ];

            foreach ($storeSettings as $setting) {
                Setting::firstOrCreate(
                    ['key' => $setting['key'], 'store_id' => $store->id],
                    [
                        'key' => $setting['key'],
                        'value' => $setting['value'],
                        'group' => $setting['group'],
                        'store_id' => $store->id,
                    ]
                );
            }
        }
    }
}
