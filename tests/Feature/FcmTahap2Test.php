<?php

namespace Tests\Feature;

use App\Models\EnrollmentSiswa;
use App\Models\FcmDeviceToken;
use App\Models\FcmNotificationLog;
use App\Models\Kelas;
use App\Models\LeaveRequest;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Notifications\AttendanceStatusNotification;
use App\Notifications\LeaveRequestResultNotification;
use App\Notifications\LeaveRequestSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FcmTahap2Test extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Siswa $student;
    protected TahunAjaran $tahunAjaran;
    protected Kelas $kelas;
    protected EnrollmentSiswa $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->user = User::factory()->create();

        $this->student = Siswa::create([
            'id'            => Str::uuid()->toString(),
            'user_id'       => $this->user->id,
            'name'          => 'Ahmad Fulan',
            'nisn'          => '1234567890',
            'jenis_kelamin' => 'L',
        ]);

        $this->enrollment = EnrollmentSiswa::create([
            'student_id'       => $this->student->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'status'           => 'aktif',
        ]);
    }

    public function test_attendance_status_notification_saves_to_database_and_fcm_logs(): void
    {
        // Daftarkan token FCM
        FcmDeviceToken::create([
            'user_id'     => $this->user->id,
            'token'       => 'test_fcm_token_student_1',
            'platform'    => 'android',
            'device_name' => 'Device 1',
        ]);

        $presensi = Presensi::create([
            'student_id'       => $this->student->id,
            'enrollment_id'    => $this->enrollment->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'date'             => now()->toDateString(),
            'status'           => 'telat',
            'late_minutes'     => 15,
        ]);

        $this->user->notify(new AttendanceStatusNotification($presensi));

        // 1. Verifikasi tersimpan di tabel notifications (Database Channel)
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => $this->user->getMorphClass(),
            'notifiable_id'   => $this->user->id,
        ]);

        $notificationRow = $this->user->notifications()->first();
        $this->assertNotNull($notificationRow);
        $data = $notificationRow->data;
        $this->assertEquals('attendance_status', $data['type']);
        $this->assertEquals('telat', $data['event_status']);
        $this->assertStringContainsString('Terlambat', $data['title']);

        // 2. Verifikasi tercatat di tabel fcm_notification_logs (FCM Channel)
        $this->assertDatabaseHas('fcm_notification_logs', [
            'user_id'      => $this->user->id,
            'type'         => 'attendance_status',
            'event_status' => 'telat',
            'status'       => 'sent',
        ]);
    }

    public function test_leave_request_submitted_notification_stores_dual_channel(): void
    {
        $teacherUser = User::factory()->create();

        $leaveRequest = LeaveRequest::create([
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'type'             => 'ijin',
            'duration_days'    => 2,
            'start_date'       => now()->toDateString(),
            'end_date'         => now()->addDays(1)->toDateString(),
            'reason'           => 'Keperluan keluarga',
            'status'           => 'pending',
        ]);

        $teacherUser->notify(new LeaveRequestSubmittedNotification($leaveRequest));

        // Verifikasi database channel
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $teacherUser->id,
        ]);

        $notif = $teacherUser->notifications()->first();
        $this->assertEquals('leave_approval', $notif->data['type']);
        $this->assertEquals('pending', $notif->data['event_status']);

        // Verifikasi FCM log
        $this->assertDatabaseHas('fcm_notification_logs', [
            'user_id'      => $teacherUser->id,
            'type'         => 'leave_approval',
            'event_status' => 'pending',
        ]);
    }

    public function test_leave_request_result_notification_stores_dual_channel(): void
    {
        $leaveRequest = LeaveRequest::create([
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'type'             => 'sakit',
            'duration_days'    => 1,
            'start_date'       => now()->toDateString(),
            'end_date'         => now()->toDateString(),
            'reason'           => 'Demam',
            'status'           => 'approved',
            'approved_at'      => now(),
        ]);

        $this->user->notify(new LeaveRequestResultNotification($leaveRequest));

        $notif = $this->user->notifications()->first();
        $this->assertEquals('leave_result', $notif->data['type']);
        $this->assertEquals('approved', $notif->data['event_status']);
        $this->assertStringContainsString('Disetujui', $notif->data['title']);

        $this->assertDatabaseHas('fcm_notification_logs', [
            'user_id'      => $this->user->id,
            'type'         => 'leave_result',
            'event_status' => 'approved',
        ]);
    }

    public function test_fcm_channel_dedup_guard_prevents_duplicate_logs(): void
    {
        $leaveRequest = LeaveRequest::create([
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'type'             => 'ijin',
            'duration_days'    => 1,
            'start_date'       => now()->toDateString(),
            'end_date'         => now()->toDateString(),
            'reason'           => 'Ada acara',
            'status'           => 'approved',
        ]);

        // Kirim pertama kali
        $this->user->notify(new LeaveRequestResultNotification($leaveRequest));
        $countAfterFirst = FcmNotificationLog::where('related_id', (string) $leaveRequest->id)->count();
        $this->assertEquals(1, $countAfterFirst);

        // Kirim kedua kali (harus di-skip oleh dedup guard di FcmChannel)
        $this->user->notify(new LeaveRequestResultNotification($leaveRequest));
        $countAfterSecond = FcmNotificationLog::where('related_id', (string) $leaveRequest->id)->count();
        $this->assertEquals(1, $countAfterSecond);
    }

    public function test_inbox_api_endpoints(): void
    {
        Sanctum::actingAs($this->user);

        // Buat 2 notifikasi dummy
        $presensi1 = Presensi::create([
            'student_id'       => $this->student->id,
            'enrollment_id'    => $this->enrollment->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'date'             => now()->subDay()->toDateString(),
            'status'           => 'alpa',
        ]);
        $presensi2 = Presensi::create([
            'student_id'       => $this->student->id,
            'enrollment_id'    => $this->enrollment->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'date'             => now()->toDateString(),
            'status'           => 'telat',
        ]);

        $this->user->notify(new AttendanceStatusNotification($presensi1));
        $this->user->notify(new AttendanceStatusNotification($presensi2));

        // 1. GET /api/v1/notifications
        $listResponse = $this->getJson('/api/v1/notifications');
        $listResponse->assertStatus(200)
                     ->assertJson([
                         'success'      => true,
                         'unread_count' => 2,
                     ]);
        $this->assertCount(2, $listResponse->json('data'));

        $firstNotifId = $listResponse->json('data.0.id');

        // 2. POST /api/v1/notifications/{id}/read
        $readResponse = $this->postJson("/api/v1/notifications/{$firstNotifId}/read");
        $readResponse->assertStatus(200)
                     ->assertJson([
                         'success'      => true,
                         'unread_count' => 1,
                     ]);

        // 3. POST /api/v1/notifications/read-all
        $readAllResponse = $this->postJson('/api/v1/notifications/read-all');
        $readAllResponse->assertStatus(200)
                        ->assertJson([
                            'success'      => true,
                            'unread_count' => 0,
                        ]);

        // Cek kembali list notifikasi
        $afterAllRead = $this->getJson('/api/v1/notifications');
        $afterAllRead->assertJson([
            'unread_count' => 0,
        ]);
    }

    public function test_inbox_accepts_legacy_fallback_token_and_rejects_invalid(): void
    {
        $fallbackToken = base64_encode($this->user->id . '|' . now()->timestamp . '|' . Str::random(40));

        $this->withHeader('Authorization', 'Bearer ' . $fallbackToken)
            ->getJson('/api/v1/notifications')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->withHeader('Authorization', 'Bearer ' . $fallbackToken)
            ->postJson('/api/v1/auth/fcm-token', [
                'token'       => 'legacy-device-token-' . Str::random(20),
                'device_type' => 'android',
            ])
            ->assertStatus(200);

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/v1/notifications')
            ->assertStatus(401);
    }
}
