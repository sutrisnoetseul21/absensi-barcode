<?php

namespace Tests\Feature;

use App\Models\FcmDeviceToken;
use App\Models\FcmNotificationLog;
use App\Models\User;
use App\Services\FcmPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FcmTahap1Test extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_fcm_token_route_requires_sanctum_authentication(): void
    {
        $response = $this->postJson('/api/v1/auth/fcm-token', [
            'token' => 'sample_token_123',
        ]);

        $response->assertStatus(401);

        $deleteResponse = $this->deleteJson('/api/v1/auth/fcm-token', [
            'token' => 'sample_token_123',
        ]);

        $deleteResponse->assertStatus(401);
    }

    public function test_authenticated_user_can_register_fcm_token(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/auth/fcm-token', [
            'token'       => 'test_fcm_token_xyz_999',
            'platform'    => 'android',
            'device_name' => 'Pixel 8 Pro (Android 14)',
            'app_version' => '1.0.8',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'FCM Device Token berhasil didaftarkan.',
                 ]);

        $this->assertDatabaseHas('fcm_device_tokens', [
            'user_id'     => $this->user->id,
            'token'       => 'test_fcm_token_xyz_999',
            'platform'    => 'android',
            'device_name' => 'Pixel 8 Pro (Android 14)',
            'app_version' => '1.0.8',
        ]);
    }

    public function test_authenticated_user_with_real_token_persists_personal_access_token_id(): void
    {
        $tokenResult = $this->user->createToken('android-test-token');
        $plainTextToken = $tokenResult->plainTextToken;
        $tokenId = $tokenResult->accessToken->id;

        $response = $this->withHeader('Authorization', 'Bearer ' . $plainTextToken)
                         ->postJson('/api/v1/auth/fcm-token', [
                             'token'       => 'token_with_real_pat_123',
                             'platform'    => 'android',
                             'device_name' => 'Real Device',
                         ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('fcm_device_tokens', [
            'user_id'                  => $this->user->id,
            'token'                    => 'token_with_real_pat_123',
            'personal_access_token_id' => $tokenId,
        ]);
    }

    public function test_delete_fcm_token_requires_token_parameter(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/auth/fcm-token', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['token']);
    }

    public function test_delete_fcm_token_only_deletes_specified_token(): void
    {
        Sanctum::actingAs($this->user);

        $token1 = FcmDeviceToken::create([
            'user_id'     => $this->user->id,
            'token'       => 'token_device_phone_' . Str::random(10),
            'platform'    => 'android',
            'device_name' => 'Phone',
        ]);

        $token2 = FcmDeviceToken::create([
            'user_id'     => $this->user->id,
            'token'       => 'token_device_tablet_' . Str::random(10),
            'platform'    => 'android',
            'device_name' => 'Tablet',
        ]);

        $response = $this->deleteJson('/api/v1/auth/fcm-token', [
            'token' => $token1->token,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'deleted' => true,
                 ]);

        $this->assertDatabaseMissing('fcm_device_tokens', [
            'id' => $token1->id,
        ]);

        $this->assertDatabaseHas('fcm_device_tokens', [
            'id' => $token2->id,
        ]);
    }

    public function test_notifications_table_accepts_uuid_notifiable_id(): void
    {
        $notificationId = Str::uuid()->toString();

        DB::table('notifications')->insert([
            'id'              => $notificationId,
            'type'            => 'App\\Notifications\\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id, // UUID string
            'data'            => json_encode(['title' => 'Test', 'body' => 'Test Body']),
            'read_at'         => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->assertDatabaseHas('notifications', [
            'id'            => $notificationId,
            'notifiable_id' => $this->user->id,
        ]);
    }

    public function test_fcm_notification_log_can_be_created(): void
    {
        $log = FcmNotificationLog::create([
            'user_id'      => $this->user->id,
            'fcm_token'    => 'dummy_token',
            'type'         => 'attendance_status',
            'related_id'   => Str::uuid()->toString(),
            'event_status' => 'telat',
            'title'        => 'Status Kehadiran',
            'body'         => 'Terlambat 10 menit',
            'status'       => 'sent',
            'sent_at'      => now(),
        ]);

        $this->assertDatabaseHas('fcm_notification_logs', [
            'id'           => $log->id,
            'event_status' => 'telat',
            'status'       => 'sent',
        ]);
    }

    public function test_fcm_push_service_instantiates_gracefully(): void
    {
        $service = app(FcmPushService::class);
        $this->assertInstanceOf(FcmPushService::class, $service);

        // When not configured, sending should return skipped gracefully without throwing exception
        $result = $service->sendToToken('dummy_token', 'Test Title', 'Test Body');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }
}
