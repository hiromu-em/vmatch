<?php
declare(strict_types=1);

namespace Vmatch;

abstract class Result
{
    protected bool $success;

    protected array $errors;

    protected string $error;

    abstract public static function success();

    abstract public static function failure(string $errorMessage);

    public function errorMessage(): string
    {
        return $this->error;
    }

    public function errorMessages(): array
    {
        return $this->errors;
    }
}