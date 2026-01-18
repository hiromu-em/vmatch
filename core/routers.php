<?php
declare(strict_types=1);

use Core\Router;
use Core\Request;
use Core\ViewRenderer;
use Vmatch\FormValidation;
use Model\UserAuthentication;
use Service\UserAuthenticationService;

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
    '/validation/email',
    [Controller\AuthController::class, 'validateEmailHandle'],
    [
        'obj' => [
            new ViewRenderer('views/UserAuthentication/'),
            new UserAuthenticationService(
                new UserAuthentication(generatePdo()),
                new FormValidation()
            )
        ]
    ]
);

