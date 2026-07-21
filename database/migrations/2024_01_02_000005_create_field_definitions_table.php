<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('description')->nullable();
            $table->string('category');
            $table->string('field_type');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_hard_filter')->default(false);
            $table->boolean('is_required')->default(true);
            // Core fields (gender/age/location, §2.1) participate in the same
            // generic weighted-scoring engine as library fields (§3) but are
            // always active and not deactivatable/re-typeable from the admin UI.
            // See ScoringService docblock for the rationale on this design choice.
            $table->boolean('is_core')->default(false);
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->integer('sort_order')->default(0);
            $table->json('config')->nullable();
            $table->timestamp('scores_stale_since')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'is_hard_filter']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_definitions');
    }
};
