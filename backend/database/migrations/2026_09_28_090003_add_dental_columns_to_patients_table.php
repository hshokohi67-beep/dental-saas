<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * chart_mode: adult (permanent only) or peds (permanent + primary shown
     * together — business rules §1.7). primary_doctor_staff_id: the doctor
     * responsible for this patient's chart, used for the legacy IDOR fix
     * (business rules §1.8) — a doctor may only edit the chart of a patient
     * they are assigned to, unlike an admin/branch manager who always can.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('chart_mode')->default('adult')->after('status');
            $table->foreignUlid('primary_doctor_staff_id')->nullable()->after('chart_mode')
                ->constrained('staff')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['primary_doctor_staff_id']);
            $table->dropColumn(['primary_doctor_staff_id', 'chart_mode']);
        });
    }
};
