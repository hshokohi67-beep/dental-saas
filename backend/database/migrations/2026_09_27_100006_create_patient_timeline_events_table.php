<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_timeline_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('patient_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['patient_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_timeline_events');
    }
};
