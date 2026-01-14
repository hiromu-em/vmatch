<?php
declare(strict_types=1);

use Routers\Router;

$router = new Router(new Request\Request());
$router->add('get', '/', [Controller\TopController::class, 'showTop']);
$router->add('get', '/login', [Controller\AuthController::class, 'showLoginForm']);
$router->add('get', '/register', [Controller\AuthController::class, 'showRegisterForm']);
$router->add('post', '/register', [Controller\AuthController::class, 'registerHandle']);