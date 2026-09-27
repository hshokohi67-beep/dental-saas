<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A shared, platform-level reference catalog of comorbidities and their
     * clinical alert text (master prompt / legacy business rules §3.1) —
     * not tenant data, so it carries no tenant_id.
     */
    public function up(): void
    {
        Schema::create('medical_conditions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key')->unique();
            $table->string('label');
            $table->text('alert_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_conditions');
    }
};
