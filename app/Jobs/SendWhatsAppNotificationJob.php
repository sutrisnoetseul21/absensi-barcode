<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhatsAppGatewayService;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function backoff(): array
    {
        // Retry bertahap: 1 menit, 5 menit, 15 menit
        return [60, 300, 900];
    }

    public function __construct(
        public string $toNumber,
        public string $message,
        public ?string $relatedType = null,
        public ?string $relatedId = null,
        public string $recipientType = 'unknown',
        public ?int $logId = null
    ) {}

    public function handle(WhatsAppGatewayService $waService): void
    {
        $waService->sendMessage(
            $this->toNumber,
            $this->message,
            $this->relatedType,
            $this->relatedId,
            $this->recipientType,
            $this->logId
        );
    }
}
