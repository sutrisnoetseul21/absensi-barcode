<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Di unit test, mock FcmPushService agar tidak menembak Google API sungguhan dengan token dummy
        $mockFcm = \Mockery::mock(\App\Services\FcmPushService::class);
        $mockFcm->shouldReceive('isConfigured')->andReturn(true)->byDefault();
        $mockFcm->shouldReceive('sendToToken')->andReturn([
            'success' => true,
            'status'  => 'sent',
            'result'  => ['name' => 'mocked-message-id'],
        ])->byDefault();
        $this->app->instance(\App\Services\FcmPushService::class, $mockFcm);
    }
}
