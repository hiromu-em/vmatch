<?php
declare(strict_types=1);

namespace Result;

use Result\Result;

class ValidationResult extends Result
{

    public function __construct(bool $success, string $authType, ?array $errors, ?string $error)
    {
        $this->success = $success;
    }

    public static function success(): ValidationResult
    {
        return new self(true, '', [], "");
    }

    public static function failure(?array $errors, ?string $error)
    {
        return new self(false, '', $errors, $error);
    }
}
