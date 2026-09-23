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
        Schema::table('konseling_bk', function (Blueprint $table) {
            $table->boolean('is_rujukan')->default(false)->after('jurnal_guru_wali_id');
            $table->uuid('konsultasi_guru_wali_id')->nullable()->after('jurnal_guru_wali_id');
            $table->text('alasan_rujukan')->nullable()->after('is_rujukan');
            
            // Allow null for some fields when it's just a rujukan that hasn't been processed
            $table->uuid('teacher_id')->nullable()->change();
            $table->string('jenis_layanan')->nullable()->change();
            $table->string('bidang_bimbingan')->nullable()->change();
            $table->string('topik_masalah')->nullable()->change();
            $table->text('uraian_kasus')->nullable()->change();
            $table->text('hasil_konseling')->nullable()->change();

            $table->foreign('konsultasi_guru_wali_id')->references('id')->on('konsultasi_guru_wali')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konseling_bk', function (Blueprint $table) {
            $table->dropForeign(['konsultasi_guru_wali_id']);
            $table->dropColumn(['is_rujukan', 'konsultasi_guru_wali_id', 'alasan_rujukan']);
        });
    }
};
