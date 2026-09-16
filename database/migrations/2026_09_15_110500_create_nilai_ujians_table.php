<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_ujians', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Proteksi data historis nilai: restrict on delete (mengikuti konvensi tabel attendances & jurnal_guru_wali)
            $table->foreignUuid('ujian_akademik_id')->constrained('ujian_akademiks')->restrictOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');

            $table->decimal('nilai_akhir', 5, 2)->default(0.00);
            $table->double('score_irt')->nullable();
            $table->boolean('is_tuntas')->default(false);

            $table->integer('jumlah_benar')->default(0);
            $table->integer('jumlah_salah')->default(0);
            $table->integer('jumlah_kosong')->default(0);

            $table->integer('violation_count')->default(0);
            $table->enum('status_kehadiran', ['hadir', 'tidak_hadir', 'susulan'])->default('hadir');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->text('catatan')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['ujian_akademik_id', 'student_id']);
            $table->index(['class_id', 'ujian_akademik_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_ujians');
    }
};
