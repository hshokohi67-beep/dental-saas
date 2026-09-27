<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The data model for auto-filling cancellations from a waitlist
     * (roadmap Phase 4 improvement note). Joining a waitlist and marking it
     * `notified`/`booked`/`expired` ships now; the actual "notify on
     * cancellation" automation is Phase 8 (Communication) — this table just
     * needs to already exist so that phase doesn't require a schema change.
     */
    public function up(): void
    {
        Schema::create('appointment_waitlist', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignUlid('dental_condition_catalog_id')->constrained('dental_condition_catalog');
            $table->date('preferred_from')->nullable();
            $table->date('preferred_until')->nullable();
            $table->string('status')->default('waiting'); // waiting|notified|booked|expired|cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_waitlist');
    }
};
