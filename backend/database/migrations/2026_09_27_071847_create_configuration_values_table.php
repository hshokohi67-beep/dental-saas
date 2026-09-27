<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuration_values', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type'); // global|plan|tenant|branch|user
            $table->string('scope_id', 26)->nullable(); // null only for scope_type=global
            $table->string('key');
            $table->json('value');
            $table->timestamps();

            $table->unique(['scope_type', 'scope_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuration_values');
    }
};
