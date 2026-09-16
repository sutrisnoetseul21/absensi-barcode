<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->enum('active_semester', ['ganjil', 'genap'])->default('ganjil')->after('academic_year_id_active');
            $table->string('cbt_driver')->nullable()->after('active_semester');
            $table->string('cbt_api_url')->nullable()->after('cbt_driver');
            $table->text('cbt_api_key')->nullable()->after('cbt_api_url');
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['active_semester', 'cbt_driver', 'cbt_api_url', 'cbt_api_key']);
        });
    }
};
