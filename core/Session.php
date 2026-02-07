<?php
declare(strict_types=1);

namespace Core;

class Session
{
    private bool $started = false;

    public function start()
    {
        if ($this->started) {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start(['use_strict_mode' => 1]);
        }

        $this->started = true;
    }

    public function setStr(string $key, string $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    public function setArray(string $key, array $values): void
    {
        $this->start();
        $_SESSION[$key] = $values;
    }

    public function getStr(string $key): string
    {
        $this->start();
        return $_SESSION[$key] ?? '';
    }

    public function getArray(string $key): array
    {
        $this->start();
        return $_SESSION[$key] ?? [];
    }

    public function getOnceStr(string $key): string
    {
        $this->start();
        $value = $_SESSION[$key] ?? '';
        unset($_SESSION[$key]);

        return $value;
    }

    public function getOnceArray(string $key): array
    {
        $this->start();
        $values = $_SESSION[$key] ?? [];
        unset($_SESSION[$key]);

        return $values;
    }

    public function has(string $key): bool
    {
        $this->start();
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        $this->start();
        unset($_SESSION[$key]);
    }

    public function clear(): void
    {
        $this->start();
        $_SESSION = [];
    }
}