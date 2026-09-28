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
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('approval_token', 8)->nullable()->unique();
            $table->timestamp('token_used_at')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('wa_text_sent_at')->nullable();
            $table->json('wa_sent_files')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn([
                'approval_token', 
                'token_used_at', 
                'token_expires_at', 
                'wa_text_sent_at', 
                'wa_sent_files'
            ]);
        });
    }
};
