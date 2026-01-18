<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../core/routers.php';

use Core\Config;

$config = new Config($_SERVER['HTTP_HOST']);

if ($config->isLocalEnvironment()) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

function generatePdo(): PDO
{
    $host = getenv('PG_LOCAL_HOST');
    $database = getenv('PG_LOCAL_DATABASE');

    $dsn = "pgsql:host={$host};port=5432;dbname={$database}";
    $user = getenv('PG_LOCAL_USER');
    $password = getenv('PG_LOCAL_PASSWORD');

    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
}


$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);