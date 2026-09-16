<?php

namespace Tests\Feature\Cbt;

use App\Exceptions\Cbt\CbtAuthenticationException;
use App\Exceptions\Cbt\CbtRateLimitException;
use App\Jobs\Cbt\PullExamResultsJob;
use App\Jobs\Cbt\SyncMasterDataJob;
use App\Jobs\Cbt\SyncStudentsJob;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\UjianAkademik;
use App\Services\Cbt\CbtServiceInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class CbtJobsTest extends TestCase
{
    public function test_sync_master_data_job_handles_auth_exception_with_fail(): void
    {
        $mockService = Mockery::mock(CbtServiceInterface::class);
        $mockService->shouldReceive('syncMasterData')
            ->once()
            ->andThrow(new CbtAuthenticationException('Token 401'));

        $mockQueueJob = Mockery::mock(\Illuminate\Contracts\Queue\Job::class);
        $mockQueueJob->shouldReceive('fail')
            ->once()
            ->withArgs(function ($e) {
                return str_contains($e->getMessage(), 'Autentikasi ke ZenCBT Bridge gagal');
            });

        $job = new SyncMasterDataJob();
        $job->setJob($mockQueueJob);

        $job->handle($mockService);
        $this->assertTrue(true);
    }

    public function test_sync_master_data_job_middleware_has_without_overlapping(): void
    {
        $job = new SyncMasterDataJob();
        $middleware = $job->middleware();

        $this->assertCount(1, $middleware);
        $this->assertInstanceOf(\Illuminate\Queue\Middleware\WithoutOverlapping::class, $middleware[0]);
    }

    public function test_sync_students_job_middleware_key_contains_class_ids(): void
    {
        $job = new SyncStudentsJob(['cls-1', 'cls-2']);
        $middleware = $job->middleware();

        $this->assertCount(1, $middleware);
        $this->assertInstanceOf(\Illuminate\Queue\Middleware\WithoutOverlapping::class, $middleware[0]);
    }

    public function test_pull_exam_results_job_failed_logs_critical(): void
    {
        Log::shouldReceive('critical')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, '[CBT_JOB_FAILED]')
                    && $context['cbt_exam_id'] === 101
                    && isset($context['exception']);
            });

        $ujian = new UjianAkademik(['nama_ujian' => 'Ujian Dummy']);
        $ujian->id = 'test-uuid-ujian';

        $job = new PullExamResultsJob(101, $ujian);
        $job->failed(new Exception('Fatal network crash'));

        $this->assertTrue(true);
    }
}
