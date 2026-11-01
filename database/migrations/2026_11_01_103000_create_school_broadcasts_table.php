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
        Schema::create('school_broadcasts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 150);
            $table->text('body');
            $table->string('target_group', 50)->default('all');
            $table->uuid('class_id')->nullable();
            $table->string('class_name', 100)->nullable();
            $table->string('sender_name', 100)->default('Admin Sekolah');
            $table->unsignedInteger('total_target_users')->default(0);
            $table->unsignedInteger('total_fcm_sent')->default(0);
            $table->string('status', 30)->default('sent');
            $table->timestamps();

            $table->index(['target_group', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_broadcasts');
    }
};
