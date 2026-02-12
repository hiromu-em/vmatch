<?php
declare(strict_types=1);

namespace Vmatch;

final class Result
{
    public function __construct(private bool $success, private string|array $value)
    {
    }

    public static function success(string|array $value = ''): Result
    {
        return new self(true, $value);
    }

    public static function failure(string|array $error): Result
    {
        return new self(false, $error);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function value(): string|array
    {
        return $this->value;
    }

    public function error(): string|array
    {
        return $this->value;
    }
}