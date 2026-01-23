<?php
declare(strict_types=1);

namespace Vmatch;

final class Result
{
    public function __construct(private bool $success, private string|array|null $error = null)
    {
    }

    public static function success(): Result
    {
        return new self(true);
    }

    public static function failure(string|array $error): Result
    {
        return new self(false, $error);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function error(): string|array
    {
        return $this->error;
    }
}