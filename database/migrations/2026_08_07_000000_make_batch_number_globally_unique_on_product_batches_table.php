<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            // MySQL uses the composite unique index to support the product_id
            // foreign key, so a plain index is needed before dropping it.
            $table->index('product_id', 'product_batches_product_id_index');
            $table->dropUnique(['product_id', 'batch_number']);
            $table->unique('batch_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropUnique(['batch_number']);
            $table->unique(['product_id', 'batch_number']);
            $table->dropIndex('product_batches_product_id_index');
        });
    }
};
