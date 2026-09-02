<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->default('fas fa-chart-bar');
            $table->string('value');
            $table->string('label');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_statistics');
    }
};
