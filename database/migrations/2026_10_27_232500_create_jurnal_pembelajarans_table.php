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
        if (!Schema::hasTable('jurnal_pembelajarans')) {
            Schema::create('jurnal_pembelajarans', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
                $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
                $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
                $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();

                $table->date('tanggal');
                $table->unsignedTinyInteger('jam_mulai');
                $table->unsignedTinyInteger('durasi_jp');
                $table->unsignedTinyInteger('jam_selesai');
                $table->string('jam_ke_label', 50);

                $table->string('materi');
                $table->text('uraian_materi');
                $table->text('refleksi')->nullable();

                $table->unsignedSmallInteger('total_siswa')->default(0);
                $table->unsignedSmallInteger('total_hadir')->default(0);
                $table->unsignedSmallInteger('total_tidak_mengikuti')->default(0);

                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['teacher_id', 'tanggal']);
                $table->index(['class_id', 'academic_year_id']);
                $table->index(['academic_year_id', 'tanggal']);
            });
        }

        if (!Schema::hasTable('jurnal_pembelajaran_presensis')) {
            Schema::create('jurnal_pembelajaran_presensis', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('jurnal_pembelajaran_id')->constrained('jurnal_pembelajarans')->cascadeOnDelete();
                $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();

                $table->enum('status_jurnal', ['hadir', 'tidak_mengikuti'])->default('hadir');
                $table->string('status_gerbang_snapshot', 50)->default('belum_absen');
                $table->string('keterangan', 255)->nullable();
                $table->boolean('diabsenkan_telat_di_gerbang')->default(false);

                $table->timestamps();

                $table->unique(['jurnal_pembelajaran_id', 'student_id'], 'jurnal_student_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_pembelajaran_presensis');
        Schema::dropIfExists('jurnal_pembelajarans');
    }
};
