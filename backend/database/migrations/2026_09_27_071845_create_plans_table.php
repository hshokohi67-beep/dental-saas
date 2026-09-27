<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key')->unique(); // e.g. "practice", "clinic", "group" — not hard-coded elsewhere
            $table->string('name');
            $table->json('limits')->nullable(); // e.g. {"max_branches": 1, "max_users": 5, "max_patients": 500}
            $table->json('features')->nullable(); // e.g. ["ai", "advanced_analytics"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
