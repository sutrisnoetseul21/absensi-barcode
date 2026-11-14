<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('school_settings', 'school_level')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->string('school_level', 10)->nullable()->default('SMP')->after('school_name');
            });
        }

        if (!Schema::hasColumn('classes', 'major')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->string('major', 100)->nullable()->after('grade_level');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('school_settings', 'school_level')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->dropColumn('school_level');
            });
        }

        if (Schema::hasColumn('classes', 'major')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropColumn('major');
            });
        }
    }
};

