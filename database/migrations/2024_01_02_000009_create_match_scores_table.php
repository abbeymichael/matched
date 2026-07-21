<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('viewer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignUuid('target_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->boolean('passed_hard_filters');
            $table->timestamp('updated_at')->nullable();

            $table->unique(['viewer_id', 'target_id']);
            $table->index(['viewer_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_scores');
    }
};
