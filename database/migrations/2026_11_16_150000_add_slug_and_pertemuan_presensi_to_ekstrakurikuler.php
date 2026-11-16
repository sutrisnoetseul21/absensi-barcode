<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->string('slug', 160)->nullable()->unique()->after('nama_ekskul');
        });

        // Backfill slug untuk ekstrakurikuler yang sudah terlanjur dibuat sebelum migrasi ini
        $existing = DB::table('ekstrakurikulers')
            ->leftJoin('academic_years', 'academic_years.id', '=', 'ekstrakurikulers.academic_year_id')
            ->select('ekstrakurikulers.id', 'ekstrakurikulers.nama_ekskul', 'academic_years.name as ay_name')
            ->get();

        $usedSlugs = [];
        foreach ($existing as $row) {
            $base = Str::slug($row->nama_ekskul . ($row->ay_name ? ' ' . $row->ay_name : '')) ?: 'ekskul';
            $slug = $base;
            $counter = 2;
            while (in_array($slug, $usedSlugs, true)) {
                $slug = "{$base}-{$counter}";
                $counter++;
            }
            $usedSlugs[] = $slug;
            DB::table('ekstrakurikulers')->where('id', $row->id)->update(['slug' => $slug]);
        }

        Schema::create('ekstrakurikuler_pertemuans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ekstrakurikuler_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
            $table->unsignedInteger('pertemuan_ke');
            $table->date('tanggal');
            $table->string('judul_kegiatan', 200)->nullable();
            $table->text('catatan_pembina')->nullable();
            $table->foreignUuid('created_by_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignUuid('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_sesi', ['aktif', 'selesai'])->default('aktif');
            $table->timestamps();

            $table->unique(['ekstrakurikuler_id', 'tanggal'], 'ekskul_pertemuan_date_unique');
            $table->index(['ekstrakurikuler_id', 'pertemuan_ke'], 'ekskul_pertemuan_ke_idx');
        });

        Schema::create('ekstrakurikuler_presensis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pertemuan_id')->constrained('ekstrakurikuler_pertemuans')->cascadeOnDelete();
            $table->foreignUuid('ekstrakurikuler_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa'])->default('hadir');
            $table->time('scan_time')->nullable();
            $table->boolean('is_manual_input')->default(false);
            $table->uuid('manual_input_by_id')->nullable();
            $table->string('manual_input_by_type')->nullable();
            $table->timestamp('manual_input_at')->nullable();
            $table->foreignUuid('manual_input_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->unique(['pertemuan_id', 'student_id'], 'ekskul_presensi_unique');
            $table->index(['ekstrakurikuler_id', 'student_id'], 'ekskul_presensi_student_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekstrakurikuler_presensis');
        Schema::dropIfExists('ekstrakurikuler_pertemuans');
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};

