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
        Schema::table('ujian_akademiks', function (Blueprint $table) {
            $table->dropColumn('jenis_ujian');
            $table->foreignId('jenis_ujian_id')->nullable()->after('cbt_event_nama')->constrained('jenis_ujians')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujian_akademiks', function (Blueprint $table) {
            $table->dropForeign(['jenis_ujian_id']);
            $table->dropColumn('jenis_ujian_id');
            $table->enum('jenis_ujian', ['harian', 'sts', 'sas', 'tryout', 'remedial'])->default('harian');
        });
    }
};
