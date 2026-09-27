<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 4 (Operations) reuses the same catalog as the bookable service
     * list instead of introducing a second one — a shift or appointment
     * points straight at a `dental_condition_catalog` row, so a color/label
     * change there is instantly reflected on the calendar too.
     */
    public function up(): void
    {
        Schema::table('dental_condition_catalog', function (Blueprint $table) {
            $table->boolean('booking_eligible')->default(false)->after('is_active');
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('booking_eligible');
            $table->unsignedSmallInteger('buffer_minutes')->default(0)->after('duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('dental_condition_catalog', function (Blueprint $table) {
            $table->dropColumn(['booking_eligible', 'duration_minutes', 'buffer_minutes']);
        });
    }
};
