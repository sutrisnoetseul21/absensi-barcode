<?php

namespace App\Exceptions\Cbt;

class CbtValidationException extends CbtException
{
    /**
     * @param array<int|string, mixed> $errors
     */
    public function __construct(
        string $message = 'Validasi data CBT gagal.',
        protected array $errors = [],
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
