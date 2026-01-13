<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../routers/routers.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);