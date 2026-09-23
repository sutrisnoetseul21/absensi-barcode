<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah tabel Chat
        Schema::create('pesan_konsultasi_guru_wali', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('konsultasi_id')->constrained('konsultasi_guru_wali')->cascadeOnDelete();
            $table->enum('sender_type', ['siswa', 'guru']);
            $table->text('pesan');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 2. Tambah kolom Feedback Note di tabel Konsultasi
        Schema::table('konsultasi_guru_wali', function (Blueprint $table) {
            $table->text('student_feedback_note')->nullable()->after('student_feedback_emoji');
        });

        // 3. Backfill data tanggapan_guru yang lama
        $konsultasis = DB::table('konsultasi_guru_wali')
            ->whereNotNull('tanggapan_guru')
            ->where('tanggapan_guru', '!=', '')
            ->get();

        foreach ($konsultasis as $k) {
            DB::table('pesan_konsultasi_guru_wali')->insert([
                'id' => (string) Str::uuid(),
                'konsultasi_id' => $k->id,
                'sender_type' => 'guru',
                'pesan' => $k->tanggapan_guru,
                'is_read' => true, // Anggap sudah terbaca karena data lama
                'created_at' => $k->updated_at ?? now(),
                'updated_at' => $k->updated_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('konsultasi_guru_wali', function (Blueprint $table) {
            $table->dropColumn('student_feedback_note');
        });

        Schema::dropIfExists('pesan_konsultasi_guru_wali');
    }
};
