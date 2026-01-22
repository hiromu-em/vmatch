<?php
declare(strict_types=1);

use Core\Router;
use Core\Request;
use Core\Response;
use Core\ViewRenderer;
use Vmatch\FormValidation;
use Service\RegisterService;
use Repository\UserAuthRepository;

$router = new Router(new Request(), new Response());
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
    '/validation/email',
    [Controller\AuthController::class, 'validateNewRegisterEmail'],
    [
        'obj' => [
            new ViewRenderer('views/UserAuthentication/'),
            new RegisterService(new UserAuthRepository(generatePdo())),
            new FormValidation()
        ]
    ]
);

