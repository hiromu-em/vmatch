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
    [new ViewRenderer('UserAuthentication')]
);
$router->add(
    'get',
    '/login',
    [Controller\AuthController::class, 'showLoginForm'],
    [new ViewRenderer('UserAuthentication')]
);
$router->add(
    'get',
    '/register',
    [Controller\AuthController::class, 'showRegisterForm'],
    [new ViewRenderer('UserAuthentication')]
);
$router->add(
    'post',
    '/register',
    [Controller\AuthController::class, 'registerHandle'],
    [new FormValidation()]
);

