<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A weekly-recurring availability block for one staff member. Preserves
     * the legacy business rules the roadmap calls out: a shift may be
     * restricted to a single bookable service (`dental_condition_catalog_id`
     * null = unrestricted) and may cap how many of that service can be
     * booked per day (`daily_cap` null = uncapped). `day_of_week` matches
     * Carbon's native 0 (Sunday) .. 6 (Saturday) so no translation layer is
     * needed when matching a shift against a calendar date.
     */
    public function up(): void
    {
        Schema::create('staff_shifts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignUlid('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignUlid('dental_condition_catalog_id')->nullable()->constrained('dental_condition_catalog')->nullOnDelete();
            $table->unsignedSmallInteger('daily_cap')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['staff_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_shifts');
    }
};
