<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_field_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('field_key');
            $table->string('value');
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['field_key', 'value']);
            $table->index('field_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_field_options');
    }
};
