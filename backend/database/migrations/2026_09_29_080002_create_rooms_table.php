<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The gap legacy never filled (roadmap risk §2). A single-chair clinic
     * never has to see this: every branch gets one `is_default` room
     * auto-created, and shifts/appointments fall back to it silently.
     * Only clinics that add more rooms interact with this concept at all.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
