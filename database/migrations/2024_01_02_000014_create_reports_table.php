<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reporter_id')->references('id')->on('users');
            $table->foreignUuid('reported_id')->references('id')->on('users');
            $table->string('reason');
            $table->text('details')->nullable();
            $table->foreignUuid('message_id')->nullable()->references('id')->on('messages')->nullOnDelete();
            $table->foreignUuid('match_id')->nullable()->references('id')->on('matches')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('severity')->default('standard');
            $table->text('admin_notes')->nullable();
            $table->string('action_taken')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->references('id')->on('users')->nullOnDelete();

            $table->index(['reported_id', 'status']);
            $table->index(['status', 'severity', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
