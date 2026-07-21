<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('match_id')->references('id')->on('matches')->cascadeOnDelete();
            $table->foreignUuid('sender_id')->references('id')->on('users');
            $table->text('body');
            $table->boolean('flagged')->default(false);
            $table->string('flag_reason')->nullable();
            $table->boolean('delivered')->default(true);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at');

            $table->index(['match_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
