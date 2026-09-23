<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konsultasi_guru_wali', function (Blueprint $table) {
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->restrictOnDelete();
            $table->foreignUuid('class_id')->nullable()->constrained('classes')->restrictOnDelete();
            $table->integer('student_feedback_rating')->nullable();
            $table->enum('student_feedback_emoji', ['Lega', 'Biasa', 'Masih Bingung'])->nullable();
        });

        // Backfill data lama
        $konsultasis = DB::table('konsultasi_guru_wali')->get();

        foreach ($konsultasis as $konsultasi) {
            $tanggalAcuan = $konsultasi->usulan_tanggal_waktu ?? $konsultasi->created_at;

            $enrollment = DB::table('student_enrollments')
                ->where('student_id', $konsultasi->student_id)
                ->where('created_at', '<=', $tanggalAcuan)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($enrollment) {
                DB::table('konsultasi_guru_wali')
                    ->where('id', $konsultasi->id)
                    ->update([
                        'academic_year_id' => $enrollment->academic_year_id,
                        'class_id' => $enrollment->class_id,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('konsultasi_guru_wali', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['class_id']);
            
            $table->dropColumn(['academic_year_id', 'class_id', 'student_feedback_rating', 'student_feedback_emoji']);
        });
    }
};
