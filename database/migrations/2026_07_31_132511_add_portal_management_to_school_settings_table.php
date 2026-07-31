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
        Schema::table('school_settings', function (Blueprint $table) {
            $table->boolean('maintenance_portal_siswa')->default(false);
            $table->boolean('maintenance_portal_guru')->default(false);
            $table->boolean('maintenance_portal_perpustakaan')->default(false);
            
            $table->text('welcome_message_siswa')->nullable();
            $table->text('welcome_message_guru')->nullable();
            $table->text('welcome_message_perpustakaan')->nullable();
            
            $table->boolean('global_announcement_active')->default(false);
            $table->text('global_announcement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn([
                'maintenance_portal_siswa',
                'maintenance_portal_guru',
                'maintenance_portal_perpustakaan',
                'welcome_message_siswa',
                'welcome_message_guru',
                'welcome_message_perpustakaan',
                'global_announcement_active',
                'global_announcement',
            ]);
        });
    }
};
