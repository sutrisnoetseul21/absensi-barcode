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
        // 1. Laporan SPIKAP utama
        Schema::create('spikap_laporan', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');

            $table->enum('sifat_laporan', ['biasa', 'darurat']);
            $table->enum('jenis_perundungan', ['fisik', 'verbal', 'sosial', 'digital', 'lainnya']);
            $table->text('uraian_kejadian');
            $table->string('lokasi_kejadian')->nullable();
            $table->dateTime('waktu_kejadian')->nullable();

            // Hanya diisi jika sifat_laporan = 'biasa'; laporan darurat otomatis ke Wali Kelas + KS
            $table->enum('tujuan_penerima', ['guru_bk', 'wali_kelas'])->nullable();

            $table->enum('status', ['diterima', 'dalam_investigasi', 'selesai'])->default('diterima');

            // Informatif saja: guru terakhir yang meng-update status, BUKAN kunci kepemilikan
            $table->foreignUuid('last_handled_by')->nullable()->constrained('teachers')->nullOnDelete();

            $table->timestamps();
        });

        // 2. Lampiran bukti (foto & video) per laporan
        Schema::create('spikap_lampiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('spikap_laporan')->onDelete('cascade');
            $table->enum('tipe_file', ['foto', 'video']);
            $table->string('path_file', 500);
            $table->string('nama_asli', 255);
            $table->unsignedBigInteger('ukuran_bytes');
            $table->timestamps();
        });

        // 3. Log perubahan status (audit trail penuh)
        Schema::create('spikap_log_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('spikap_laporan')->onDelete('cascade');
            $table->string('status_lama', 50)->nullable();
            $table->string('status_baru', 50);
            $table->text('catatan')->nullable();
            // NULL berarti dicatat oleh sistem (bukan guru), misal fallback wali kelas tidak ditemukan
            $table->foreignUuid('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 4. Konfigurasi penerima notifikasi WA untuk laporan darurat (singleton baris id=1)
        Schema::create('spikap_notif_settings', function (Blueprint $table) {
            $table->id();
            // Satu-satunya sumber kebenaran penerima. Contoh: ["wali_kelas","kepala_sekolah"]
            // Key di-resolve via RecipientResolverService yang sudah ada
            // CATATAN: MySQL tidak mendukung DEFAULT pada kolom JSON.
            // Nilai default diisi oleh SpikapNotifSetting::instance() di seeder.
            $table->json('recipients')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spikap_notif_settings');
        Schema::dropIfExists('spikap_log_status');
        Schema::dropIfExists('spikap_lampiran');
        Schema::dropIfExists('spikap_laporan');
    }
};
