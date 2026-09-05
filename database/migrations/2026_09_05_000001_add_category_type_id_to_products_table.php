<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_type_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
        });

        $categoryTypeByCategory = DB::table('categories')->pluck('category_type_id', 'id');

        DB::table('products')->whereNull('category_type_id')->select('id', 'category_id')->orderBy('id')
            ->get()
            ->each(function (object $product) use ($categoryTypeByCategory): void {
                $categoryTypeId = $categoryTypeByCategory[$product->category_id] ?? null;

                if ($categoryTypeId) {
                    DB::table('products')->where('id', $product->id)->update(['category_type_id' => $categoryTypeId]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_type_id']);
            $table->dropColumn('category_type_id');
        });
    }
};
