<?php

namespace App\Jobs\Cbt;

use App\Exceptions\Cbt\CbtAuthenticationException;
use App\Exceptions\Cbt\CbtRateLimitException;
use App\Services\Cbt\CbtServiceInterface;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncMasterDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('cbt:sync-master'))
                ->dontRelease()
                ->expireAfter(120),
        ];
    }

    public function handle(CbtServiceInterface $cbtService): void
    {
        try {
            $cbtService->syncMasterData();
        } catch (CbtAuthenticationException $e) {
            $this->fail(new Exception("Autentikasi ke ZenCBT Bridge gagal, periksa CBT_API_KEY: " . $e->getMessage(), 0, $e));
        } catch (CbtRateLimitException $e) {
            $this->release($e->getRetryAfter() ?: 60);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::critical("[CBT_JOB_FAILED] SyncMasterDataJob gagal permanen.", [
            'job'       => self::class,
            'exception' => get_class($exception),
            'message'   => $exception->getMessage(),
            'trace'     => $exception->getTraceAsString(),
        ]);
    }
}
