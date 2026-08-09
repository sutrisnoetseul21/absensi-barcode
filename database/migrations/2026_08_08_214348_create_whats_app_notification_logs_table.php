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
        Schema::create('whatsapp_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('recipient_type')->comment('ortu, wali_kelas, dll');
            $table->string('recipient_number');
            $table->text('message');
            $table->string('status')->default('pending')->comment('pending, sent, failed');
            $table->text('response_payload')->nullable();
            $table->string('related_type')->nullable();
            $table->uuid('related_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index(['related_type', 'related_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notification_logs');
    }
};
