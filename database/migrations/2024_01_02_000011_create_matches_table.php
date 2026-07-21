<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_a_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignUuid('user_b_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('matched_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_a_id', 'user_b_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
