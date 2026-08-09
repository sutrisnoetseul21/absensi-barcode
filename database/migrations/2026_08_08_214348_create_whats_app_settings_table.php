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
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module')->unique()->comment('Contoh: presensi, perpustakaan');
            $table->string('base_url')->nullable();
            $table->text('api_key')->nullable();
            $table->string('instance_name')->nullable();
            $table->string('sender_number')->nullable();
            $table->boolean('is_active')->default(false);
            $table->integer('delay_between_messages_seconds')->default(4);
            $table->time('send_window_start')->nullable()->default('06:00:00');
            $table->time('send_window_end')->nullable()->default('17:00:00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_settings');
    }
};
