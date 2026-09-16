<?php

namespace App\Jobs\Cbt;

use App\Exceptions\Cbt\CbtAuthenticationException;
use App\Exceptions\Cbt\CbtRateLimitException;
use App\Models\UjianAkademik;
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

class PullExamResultsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public string|int $cbtExamId,
        public UjianAkademik $ujianAkademik
    ) {}

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("cbt:pull-results:{$this->ujianAkademik->id}"))
                ->dontRelease()
                ->expireAfter(180),
        ];
    }

    public function handle(CbtServiceInterface $cbtService): void
    {
        try {
            $cbtService->pullExamResults($this->cbtExamId, $this->ujianAkademik);
        } catch (CbtAuthenticationException $e) {
            $this->fail(new Exception("Autentikasi ke ZenCBT Bridge gagal, periksa CBT_API_KEY: " . $e->getMessage(), 0, $e));
        } catch (CbtRateLimitException $e) {
            $this->release($e->getRetryAfter() ?: 60);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::critical("[CBT_JOB_FAILED] PullExamResultsJob gagal permanen saat menarik nilai ujian.", [
            'job'               => self::class,
            'cbt_exam_id'       => $this->cbtExamId,
            'ujian_akademik_id' => $this->ujianAkademik->id,
            'nama_ujian'        => $this->ujianAkademik->nama_ujian,
            'exception'         => get_class($exception),
            'message'           => $exception->getMessage(),
            'trace'             => $exception->getTraceAsString(),
        ]);
    }
}
