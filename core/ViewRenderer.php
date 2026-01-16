<?php
declare(strict_types=1);

namespace Core;

class ViewRenderer
{
    private string $basePath = __DIR__ . '/public/resources/views/';

    public function __construct(private string $directoryPath = "")
    {
    }
}