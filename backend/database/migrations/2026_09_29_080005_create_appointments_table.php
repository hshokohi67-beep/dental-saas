<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `treatment_plan_step_id` is intentionally unconstrained (no FK) — the
     * table it will eventually point at doesn't exist until Phase 6
     * (Treatment Planning). Reserving the column now avoids an awkward
     * migration later; it is simply left null until then.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignUlid('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignUlid('dental_condition_catalog_id')->constrained('dental_condition_catalog');
            $table->ulid('treatment_plan_step_id')->nullable();
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes');
            $table->string('status')->default('booked'); // booked|confirmed|checked_in|completed|cancelled|no_show
            $table->text('notes')->nullable();
            $table->string('cancelled_reason')->nullable();
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['staff_id', 'scheduled_at']);
            $table->index(['branch_id', 'scheduled_at']);
            $table->index(['patient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
