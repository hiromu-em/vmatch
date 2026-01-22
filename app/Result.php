<?php
declare(strict_types=1);

namespace Vmatch;

final class Result
{
    public function __construct(private bool $success, private array $messages = [])
    {
    }

    public static function success(): Result
    {
        return new self(true);
    }

    public static function failure(array $errorMessages): Result
    {
        return new self(false, $errorMessages);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}