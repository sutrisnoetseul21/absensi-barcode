<?php

namespace Tests\Feature;

use App\Models\EnrollmentSiswa;
use App\Models\FcmDeviceToken;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\KelasAjaran;
use App\Models\LeaveRequest;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\AlpaAutomationService;
use App\Services\KalenderSekolahService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FcmTahap3Test extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Siswa $student;
    protected TahunAjaran $tahunAjaran;
    protected Kelas $kelas;
    protected EnrollmentSiswa $enrollment;
    protected User $waliUser;
    protected Guru $waliGuru;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock KalenderSekolahService agar selalu menganggap hari ini hari sekolah
        $kalenderMock = $this->createMock(KalenderSekolahService::class);
        $kalenderMock->method('isHariSekolah')->willReturn(true);
        $this->app->instance(KalenderSekolahService::class, $kalenderMock);

        $this->tahunAjaran = TahunAjaran::create([
            'name'       => '2026/2027',
            'start_year' => 2026,
            'end_year'   => 2027,
            'status'     => 'aktif',
        ]);

        $this->kelas = Kelas::create([
            'name'        => 'VII-A',
            'grade_level' => 7,
        ]);

        // Akun Siswa & Model Siswa
        $this->user = User::factory()->create();

        $this->student = Siswa::create([
            'id'            => Str::uuid()->toString(),
            'user_id'       => $this->user->id,
            'name'          => 'Budi Santoso',
            'nisn'          => '1234567890',
            'jenis_kelamin' => 'L',
        ]);

        $this->enrollment = EnrollmentSiswa::create([
            'student_id'       => $this->student->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'status'           => 'aktif',
        ]);

        // Akun Wali Kelas & Guru
        $this->waliUser = User::factory()->create(['name' => 'Wali Kelas Ibu Siti']);
        $this->waliGuru = Guru::create([
            'id'      => Str::uuid()->toString(),
            'user_id' => $this->waliUser->id,
            'name'    => 'Ibu Siti Aminah',
            'no_hp'   => '081234567890',
        ]);

        KelasAjaran::create([
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'teacher_id'       => $this->waliGuru->id,
        ]);

        PengaturanSekolah::create([
            'school_name'              => 'SMP Negeri 1 Testing',
            'academic_year_id_active'  => $this->tahunAjaran->id,
            'auto_alpa_active'         => true,
            'auto_alpa_time'           => '09:00:00',
        ]);
    }

    public function test_telat_scan_triggers_attendance_notification_to_student(): void
    {
        FcmDeviceToken::create([
            'user_id'     => $this->user->id,
            'token'       => 'fcm_token_student_telat',
            'platform'    => 'android',
            'device_name' => 'Student Phone',
        ]);

        // Buat presensi telat hari ini
        Presensi::create([
            'student_id'       => $this->student->id,
            'enrollment_id'    => $this->enrollment->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'date'             => now()->toDateString(),
            'status'           => 'telat',
            'late_minutes'     => 25,
            'scan_time'        => '07:25:00',
        ]);

        // Verifikasi tabel notifications (In-App database)
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->user->id,
        ]);

        $notif = $this->user->notifications()->first();
        $this->assertNotNull($notif);
        $this->assertEquals('attendance_status', $notif->data['type']);
        $this->assertEquals('telat', $notif->data['event_status']);

        // Verifikasi tabel fcm_notification_logs (Push FCM channel)
        $this->assertDatabaseHas('fcm_notification_logs', [
            'user_id'      => $this->user->id,
            'type'         => 'attendance_status',
            'event_status' => 'telat',
            'status'       => 'sent',
        ]);
    }

    public function test_hadir_or_libur_or_leave_request_id_does_not_trigger_attendance_notification(): void
    {
        // 1. Status 'hadir' tidak boleh memicu push notification
        Presensi::create([
            'student_id'       => $this->student->id,
            'enrollment_id'    => $this->enrollment->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'date'             => now()->toDateString(),
            'status'           => 'hadir',
            'scan_time'        => '06:45:00',
        ]);

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $this->user->id,
        ]);
        $this->assertDatabaseMissing('fcm_notification_logs', [
            'user_id' => $this->user->id,
        ]);

        // Buat siswa kedua untuk skenario 'libur'
        $user2 = User::factory()->create();
        $student2 = Siswa::create([
            'id'            => Str::uuid()->toString(),
            'user_id'       => $user2->id,
            'name'          => 'Siti Siswi',
            'nisn'          => '9876543210',
            'jenis_kelamin' => 'P',
        ]);
        $enrollment2 = EnrollmentSiswa::create([
            'student_id'       => $student2->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'status'           => 'aktif',
        ]);

        // 2. Status 'libur' tidak boleh memicu notifikasi
        Presensi::create([
            'student_id'       => $student2->id,
            'enrollment_id'    => $enrollment2->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'date'             => now()->toDateString(),
            'status'           => 'libur',
        ]);

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user2->id,
        ]);

        // Buat siswa ketiga untuk skenario 'leave_request_id'
        $user3 = User::factory()->create();
        $student3 = Siswa::create([
            'id'            => Str::uuid()->toString(),
            'user_id'       => $user3->id,
            'name'          => 'Doni Siswa',
            'nisn'          => '1122334455',
            'jenis_kelamin' => 'L',
        ]);
        $enrollment3 = EnrollmentSiswa::create([
            'student_id'       => $student3->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'status'           => 'aktif',
        ]);

        $lr = LeaveRequest::create([
            'student_id'       => $student3->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'type'             => 'sakit',
            'duration_days'    => 1,
            'start_date'       => now()->toDateString(),
            'end_date'         => now()->toDateString(),
            'reason'           => 'Demam',
            'status'           => 'approved',
        ]);

        // 3. Presensi yang memiliki leave_request_id (sudah dinotifikasi via izin) tidak boleh double-trigger
        Presensi::create([
            'student_id'       => $student3->id,
            'enrollment_id'    => $enrollment3->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'date'             => now()->toDateString(),
            'status'           => 'sakit',
            'leave_request_id' => $lr->id,
        ]);

        // Presensi ber-leave_request_id TIDAK boleh memicu notifikasi attendance_status
        $this->assertDatabaseMissing('fcm_notification_logs', [
            'user_id' => $user3->id,
            'type'    => 'attendance_status',
        ]);
    }

    public function test_student_submitting_leave_request_pending_notifies_wali_kelas(): void
    {
        FcmDeviceToken::create([
            'user_id'     => $this->waliUser->id,
            'token'       => 'fcm_token_wali_kelas_1',
            'platform'    => 'android',
            'device_name' => 'Wali Kelas Phone',
        ]);

        $leaveRequest = LeaveRequest::create([
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'type'             => 'ijin',
            'duration_days'    => 2,
            'start_date'       => now()->toDateString(),
            'end_date'         => now()->addDays(1)->toDateString(),
            'reason'           => 'Acara keluarga di luar kota',
            'status'           => 'pending',
        ]);

        // Verifikasi Wali Kelas menerima notifikasi
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->waliUser->id,
        ]);

        $notif = $this->waliUser->notifications()->first();
        $this->assertNotNull($notif);
        $this->assertEquals('leave_approval', $notif->data['type']);
        $this->assertEquals((string) $leaveRequest->id, $notif->data['id']);

        $this->assertDatabaseHas('fcm_notification_logs', [
            'user_id'      => $this->waliUser->id,
            'type'         => 'leave_approval',
            'event_status' => 'pending',
            'status'       => 'sent',
        ]);
    }

    public function test_leave_request_approval_notifies_student(): void
    {
        FcmDeviceToken::create([
            'user_id'     => $this->user->id,
            'token'       => 'fcm_token_student_result',
            'platform'    => 'android',
            'device_name' => 'Student Phone',
        ]);

        $leaveRequest = LeaveRequest::create([
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'type'             => 'sakit',
            'duration_days'    => 1,
            'start_date'       => now()->toDateString(),
            'end_date'         => now()->toDateString(),
            'reason'           => 'Flu berat',
            'status'           => 'pending',
        ]);

        // Wali kelas menyetujui (via model update)
        $leaveRequest->update([
            'status'      => 'approved',
            'approved_at' => now(),
        ]);

        // Verifikasi Siswa menerima notifikasi hasil
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->user->id,
        ]);

        $notif = $this->user->notifications()->first();
        $this->assertNotNull($notif);
        $this->assertEquals('leave_result', $notif->data['type']);
        $this->assertEquals('approved', $notif->data['event_status']);

        $this->assertDatabaseHas('fcm_notification_logs', [
            'user_id'      => $this->user->id,
            'type'         => 'leave_result',
            'event_status' => 'approved',
            'status'       => 'sent',
        ]);
    }

    public function test_kiosk_failure_guard_stops_auto_alpa_cron_when_zero_scans(): void
    {
        // Kondisi: Belum ada seorang pun siswa yang scan datang hari ini
        $result = AlpaAutomationService::process('cron');

        $this->assertEquals('warning', $result['status']);
        $this->assertEquals(0, $result['count']);
        $this->assertStringContainsString('Kiosk Failure Guard', $result['message']);

        // Pastikan tidak ada record Presensi alpa yang terbuat
        $this->assertDatabaseMissing('attendances', [
            'status' => 'alpa',
        ]);
    }
}
