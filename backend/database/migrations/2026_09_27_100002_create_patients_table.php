<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Patient is a tenant-level entity (target architecture §3) — it is never
     * duplicated per branch; branch_id here is only the registering branch,
     * used as context, not as a scoping boundary.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('mobile');
            $table->string('national_id', 10)->nullable();
            $table->string('gender')->nullable(); // male|female|other
            $table->date('date_of_birth')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active'); // active|archived|merged
            $table->foreignUlid('merged_into_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'mobile']);
            $table->index(['tenant_id', 'national_id']);
            $table->index(['tenant_id', 'last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
