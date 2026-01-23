<?php
declare(strict_types=1);

namespace Core;

class Response
{
    public function redirect(string $uri, int $status = 303): never
    {
        header("Location: $uri", true, $status);
        exit;
    }
}