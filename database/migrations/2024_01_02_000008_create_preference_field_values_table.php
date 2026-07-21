<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preference_field_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('field_key');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'field_key']);
            $table->index(['user_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preference_field_values');
    }
};
