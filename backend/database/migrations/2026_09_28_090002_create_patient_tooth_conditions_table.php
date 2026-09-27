<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replaces the legacy `dental_tooth_conditions`' overloaded tooth_number
     * encoding (business rules §1.6: half-arch and whole-mouth findings were
     * squeezed into the same tooth_number column) with an explicit scope
     * model: tooth / quadrant / arch / whole_mouth (roadmap Phase 3).
     * No hard delete of clinical records — a condition is voided, not
     * removed, per the target architecture's audit rule.
     */
    public function up(): void
    {
        Schema::create('patient_tooth_conditions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('dental_condition_catalog_id')->constrained('dental_condition_catalog')->cascadeOnDelete();
            $table->string('scope_type'); // tooth|quadrant|arch|whole_mouth
            $table->unsignedSmallInteger('tooth_number')->nullable();
            $table->unsignedTinyInteger('quadrant')->nullable();
            $table->string('arch')->nullable(); // upper|lower
            $table->json('surfaces')->nullable(); // mesial|distal|occlusal|incisal|buccal|lingual
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at');
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['patient_id', 'tooth_number']);
            $table->index(['patient_id', 'scope_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_tooth_conditions');
    }
};
