<?php

namespace App\Services\Cbt;

use App\Models\UjianAkademik;

interface CbtServiceInterface
{
    /**
     * Uji konektivitas sederhana ke root bridge API.
     */
    public function ping(): bool;

    /**
     * Periksa kesehatan menyeluruh bridge dan database ZenCBT.
     * 
     * @return array<string, mixed>
     */
    public function healthCheck(): array;

    /**
     * Sinkronisasi data master (Tahun Ajaran, Tingkat, Rombel, Mata Pelajaran) ke ZenCBT.
     * 
     * @return array<string, mixed>
     */
    public function syncMasterData(): array;

    /**
     * Sinkronisasi siswa ke ZenCBT secara batch.
     * 
     * @param array<string>|null $classIds Filter ID kelas, atau null untuk semua kelas aktif
     * @return array<string, mixed>
     */
    public function syncStudents(?array $classIds = null): array;

    /**
     * Mengambil daftar ujian dari ZenCBT.
     * 
     * @return array<int, array<string, mixed>>
     */
    public function getExams(): array;

    /**
     * Mempratinjau data ujian dari CBT Engine untuk disinkronkan.
     *
     * @return array<string, mixed> Data pratinjau
     */
    public function previewSyncExams(): array;

    /**
     * Memperbarui jadwal ujian dari CBT Engine ke database lokal.
     *
     * @param array $exams Optional array of raw exam data to sync directly
     * @return array<string, mixed> Informasi hasil sinkronisasi
     */
    public function syncExams(array $exams = []): array;

    /**
     * Menarik hasil nilai ujian dari ZenCBT dan menyimpan ke tabel nilai_ujians.
     * 
     * @return array<string, mixed> Ringkasan penarikan: total_results, synced, skipped
     */
    public function pullExamResults(string|int $cbtExamId, UjianAkademik $ujianAkademik): array;

    /**
     * Mengubah sesi ujian siswa secara eksplisit.
     * 
     * @return array<string, mixed>
     */
    public function setStudentSession(string $nisn, string $session): array;

    /**
     * Reset status blokir dan session token siswa di ZenCBT.
     * 
     * @return array<string, mixed>
     */
    public function resetStudentSession(string $nisn): array;

    /**
     * Sinkronisasi data Guru ke ZenCBT (buat akun user role 'guru').
     * Password hanya diset saat INSERT pertama, tidak diubah saat update.
     * Logika Ekspansi Tingkat: guru mendapat akses ke semua kelas
     * dalam tingkat yang sama dengan kelas yang diajarnya.
     *
     * @param  array<string>|null $guruIds ID Guru yang akan disinkron, null = semua guru aktif
     * @return array<string, mixed>
     */
    public function syncTeachers(?array $guruIds = null): array;

    /**
     * Reset password guru di ZenCBT ke nilai default (NIP@03 atau NamaLengkapTanpaSpasi).
     *
     * @param  string $guruId UUID guru di database Laravel
     * @return array<string, mixed>
     */
    public function resetTeacherPassword(string $guruId): array;

    /**
     * Update password guru di ZenCBT ke password baru yang ditentukan Admin.
     *
     * @param  string $guruId   UUID guru di database Laravel
     * @param  string $password Password baru plain-text
     * @return array<string, mixed>
     */
    public function updateTeacherPassword(string $guruId, string $password): array;
}
