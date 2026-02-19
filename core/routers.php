<?php
declare(strict_types=1);

use Core\Router;
use Core\Request;
use Core\Response;
use Core\ViewRenderer;
use Core\Session;
use Service\UserRegisterService;
use Service\UserLoginService;
use Repository\UserAuthRepository;
use Vmatch\FormValidation;
use Vmatch\GoogleOauth;

use Google\Client;

$router = new Router(
    new Request($_GET, $_POST, $_SERVER),
    new Response(),
    new Session()
);

// 環境変数の読み込み
loadenv();

$router->add(
    'GET',
    '/',
    ['class' => Controller\TopController::class, 'method' => 'showTop'],
    [new ViewRenderer()]
);

$router->add(
    'GET',
    '/login',
    ['class' => Controller\UserAuthController::class, 'method' => 'showLoginForm'],
    [new ViewRenderer('views/UserAuthentication/')]
);

$router->add(
    'GET',
    '/register',
    ['class' => Controller\UserAuthController::class, 'method' => 'showRegisterForm'],
    [new ViewRenderer('views/UserAuthentication/')]
);

$router->add(
    'POST',
    '/verification-email',
    ['class' => Controller\UserAuthController::class, 'method' => 'handleRegisterEmailVerification'],
    [
        new UserRegisterService(new UserAuthRepository(generatePdo())),
        new FormValidation()
    ]
);

$router->add(
    'GET',
    '/token-verification',
    ['class' => Controller\UserAuthController::class, 'method' => 'handleTokenVerification'],
    [new UserRegisterService(new UserAuthRepository(generatePdo()))]
);

$router->add(
    'GET',
    '/new-password-setting',
    ['class' => Controller\UserAuthController::class, 'method' => 'showNewPasswordSetting'],
    [new ViewRenderer('views/UserAuthentication/')]
);

$router->add(
    'POST',
    '/user-rgister',
    ['class' => Controller\UserAuthController::class, 'method' => 'handleNewUserRegister'],
    [
        new UserRegisterService(new UserAuthRepository(generatePdo())),
        new FormValidation(),
        new ViewRenderer('views/Error/')
    ]
);

$router->add(
    'GET',
    '/init-profile-setting',
    ['class' => Controller\UserSettingController::class, 'method' => 'showInitProfileSetting'],
    [new ViewRenderer()]
);

$router->add(
    'POST',
    '/user-login',
    ['class' => Controller\UserAuthController::class, 'method' => 'handleUserLogin'],
    [
        new FormValidation(),
        new UserLoginService(new UserAuthRepository(generatePdo())),
        new ViewRenderer('views/Error/')
    ]
);

$router->add(
    'GET',
    '/google-oauth',
    ['class' => Controller\OauthController::class, 'method' => 'handleGoogleOauth'],
    [
        new GoogleOauth(new Client()),
        ['client_id' => $_ENV['CLIENTID'], 'client_secret' => $_ENV['CLIENTSECRET']]
    ]
);

$router->add(
    'GET',
    '/google-oauth-code',
    ['class' => Controller\OauthController::class, 'method' => 'handleGoogleOauthCode'],
    [
        new GoogleOauth(new Client(
            ['client_id' => $_ENV['CLIENTID'], 'client_secret' => $_ENV['CLIENTSECRET']]
        ))
    ]
);