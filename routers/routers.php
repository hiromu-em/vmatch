<?php
declare(strict_types=1);

use Routers\Router;
use Requests\Requests;

$router = new Router(new Requests());
$router->add('get', '/', [Controller\TopController::class, 'showTop']);
$router->add('get', '/login', [Controller\AuthController::class, 'showLoginForm']);
$router->add('get', '/register', [Controller\AuthController::class, 'showRegisterForm']);