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
        Schema::create('fcm_notification_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('fcm_token', 500)->nullable();
            $table->string('type', 50); // attendance_status, leave_approval, leave_result, consultation, spikap
            $table->string('related_id', 100);
            $table->string('event_status', 50); // e.g. pending, approved, rejected, telat, alpa
            $table->string('title');
            $table->text('body');
            $table->json('payload')->nullable();
            $table->string('status', 20)->default('pending'); // pending, sent, failed
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'related_id', 'event_status'], 'fcm_notif_dedup_idx');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcm_notification_logs');
    }
};
