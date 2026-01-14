<?php
declare(strict_types=1);

namespace Controller;

use Request\Request;

class TopController
{
    public function __construct(private Request $request)
    {
    }

    public function showTop(): void
    {
        include __DIR__ . '/../../public/resources/views/top.php';
    }
}