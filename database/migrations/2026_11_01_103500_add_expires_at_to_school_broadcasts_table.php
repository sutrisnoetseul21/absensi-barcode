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
        Schema::table('school_broadcasts', function (Blueprint $table) {
            $table->unsignedSmallInteger('active_hours')->default(10)->after('sender_name');
            $table->timestamp('expires_at')->nullable()->after('active_hours')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_broadcasts', function (Blueprint $table) {
            $table->dropColumn(['active_hours', 'expires_at']);
        });
    }
};
