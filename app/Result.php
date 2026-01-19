<?php
declare(strict_types=1);

abstract class Result
{
    protected bool $success;

    protected array $errors;

    protected string $error;

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function errorMessage(): string
    {
        return $this->error;
    }

    public function errorMessages(): array
    {
        return $this->errors;
    }
}