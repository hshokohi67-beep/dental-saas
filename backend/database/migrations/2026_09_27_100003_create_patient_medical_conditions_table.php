<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_medical_conditions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('medical_condition_id')->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->unique(['patient_id', 'medical_condition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_medical_conditions');
    }
};
