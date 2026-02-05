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
        Schema::create('sale_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->string('prescription_number');
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('patient_name');
            $table->integer('patient_age')->nullable();
            $table->text('patient_address')->nullable();
            $table->text('diagnosis')->nullable();
            $table->date('date');
            $table->boolean('is_copy')->default(false);
            $table->integer('copy_number')->default(1);
            $table->timestamps();

            $table->index('prescription_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_prescriptions');
    }
};
