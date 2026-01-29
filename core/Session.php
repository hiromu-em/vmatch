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
            session_start();
        }

        $this->started = true;
    }

    public function set(string $key, string $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    public function get(string $key): string
    {
        $this->start();
        return $_SESSION[$key] ?? '';
    }

    public function remove(string $key): void
    {
        $this->start();
        unset($_SESSION[$key]);
    }

    public function getOnce(string $key): string
    {
        $this->start();
        $value = $_SESSION[$key] ?? '';
        unset($_SESSION[$key]);

        return $value;
    }

    public function clear()
    {
        $this->start();
        $_SESSION = [];
    }
}