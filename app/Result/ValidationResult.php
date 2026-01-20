<?php
declare(strict_types=1);

namespace Result;

use Result\Result;

class ValidationResult extends Result
{

    public function __construct(bool $success, string $message = "")
    {
        $this->success = $success;
        $this->error = $message;
    }

    public static function success(): ValidationResult
    {
        return new self(true);
    }

    public static function failure(string $errorMessage): ValidationResult
    {
        return new self(false, $errorMessage);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
