<?php

namespace App\Exceptions\Cbt;

class CbtRateLimitException extends CbtException
{
    public function __construct(
        string $message = 'Pembatasan laju request ZenCBT terlampaui (HTTP 429).',
        protected int $retryAfter = 60,
        int $code = 429,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter > 0 ? $this->retryAfter : 60;
    }
}
