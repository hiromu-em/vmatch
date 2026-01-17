<?php
declare(strict_types=1);

use Core\Router;
use Core\Request;
use Core\ViewRenderer;
use Vmatch\FormValidation;

$router = new Router(new Request());
$router->add(
    'get',
    '/',
    [Controller\TopController::class, 'showTop'],
    ['obj' => new ViewRenderer('views/')]
);
$router->add(
    'get',
    '/login',
    [Controller\AuthController::class, 'showLoginForm'],
    ['obj' => new ViewRenderer('views/UserAuthentication/')]
);
$router->add(
    'get',
    '/register',
    [Controller\AuthController::class, 'showRegisterForm'],
    ['obj' => new ViewRenderer('views/UserAuthentication/')]
);
$router->add(
    'post',
    '/register',
    [Controller\AuthController::class, 'registerHandle'],
    ['obj' => [new FormValidation(), new ViewRenderer('views/UserAuthentication/')]]
);

