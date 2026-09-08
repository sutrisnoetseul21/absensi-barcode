<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('web_pillars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('tag')->nullable();
            $table->text('desc')->nullable();
            $table->string('icon')->default('fas fa-star');
            $table->string('color_theme')->default('blue');
            $table->string('link')->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_pillars');
    }
};
