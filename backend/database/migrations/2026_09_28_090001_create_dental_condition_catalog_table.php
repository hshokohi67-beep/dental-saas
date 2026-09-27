<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A shared, platform-level catalog of dental treatment/diagnostic codes
     * (business rules §1.3, §1.5) — not tenant data, mirroring the pattern
     * used for `medical_conditions`. `status_priority` (lower wins) and the
     * colors drive the derived tooth status shown on the odontogram; codes
     * with no priority never determine a tooth's displayed color on their
     * own (business rules §1.3 — a tooth with only such codes gets the
     * dashed "no dedicated color" state, never a false "healthy" look).
     */
    public function up(): void
    {
        Schema::create('dental_condition_catalog', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('category'); // treatment|diagnostic|radiograph|consultation|other
            $table->string('scope'); // tooth|half_arch|arch|whole_mouth
            $table->json('dentitions'); // subset of [permanent, primary]
            $table->unsignedSmallInteger('status_priority')->nullable();
            $table->string('status_color', 7)->nullable();
            $table->string('status_border', 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_condition_catalog');
    }
};
