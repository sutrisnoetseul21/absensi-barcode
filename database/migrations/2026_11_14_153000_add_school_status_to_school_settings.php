<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('school_settings', 'school_status')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->string('school_status', 10)->nullable()->default('negeri')->after('school_level');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('school_settings', 'school_status')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->dropColumn('school_status');
            });
        }
    }
};

