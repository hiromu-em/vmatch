<?php
declare(strict_types=1);

use Core\Router;
use Core\Request;
use Core\Response;
use Core\ViewRenderer;
use Core\Session;
use Vmatch\FormValidation;
use Service\UserRegisterService;
use Repository\UserAuthRepository;

$router = new Router(
    new Request($_GET, $_POST, $_SERVER),
    new Response(),
    new Session()
);

$router->add(
    'GET',
    '/',
    ['class' => Controller\TopController::class, 'method' => 'showTop'],
    [new ViewRenderer()]
);

$router->add(
    'GET',
    '/login',
    ['class' => Controller\AuthController::class, 'method' => 'showLoginForm'],
    [new ViewRenderer('views/UserAuthentication/')]
);

$router->add(
    'GET',
    '/register',
    ['class' => Controller\AuthController::class, 'method' => 'showRegisterForm'],
    [new ViewRenderer('views/UserAuthentication/')]
);

$router->add(
    'POST',
    '/verification-email',
    ['class' => Controller\AuthController::class, 'method' => 'handleRegisterEmailVerification'],
    [
        new UserRegisterService(new UserAuthRepository(generatePdo())),
        new FormValidation()
    ]
);

$router->add(
    'GET',
    '/token-verification',
    ['class' => Controller\AuthController::class, 'method' => 'handleTokenVerification'],
    [new UserRegisterService(new UserAuthRepository(generatePdo()))]
);

$router->add(
    'GET',
    '/new-password-setting',
    ['class' => Controller\AuthController::class, 'method' => 'showNewPasswordSetting'],
    [new ViewRenderer('views/UserAuthentication/')]
);

$router->add(
    'POST',
    '/user-rgister',
    ['class' => Controller\AuthController::class, 'method' => 'handleNewUserRegister'],
    [
        new UserRegisterService(new UserAuthRepository(generatePdo())),
        new FormValidation(),
        new ViewRenderer('views/Error/')
    ]
);

$router->add(
    'GET',
    '/init-profile-settng',
    ['class' => Controller\UserSettingController::class, 'method' => 'showInitProfileSettng'],
    [new ViewRenderer()]
);