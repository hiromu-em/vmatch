<?php
declare(strict_types=1);

use Vmatch\Oauth\GoogleAuthorization;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start(['use_strict_mode' => 1]);

const GOOGLE_OAUTH_REDIRECT = 'googleOauth.php';

$googleAuthorization = new GoogleAuthorization();
$client = $googleAuthorization->clientConfig();

// 認可コードがない場合、認可サーバーのURLを生成
if (!isset($_GET['code'])) {

    $authUrl = $googleAuthorization->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}

// CSRF対策：stateを検証
if ($_GET['state'] !== $_SESSION['google_oauth_state']) {
    
    $_SESSION['google_oauth_state'];
    exit('Invalid state');
}

unset($_SESSION['google_oauth_state']);

//コードをアクセストークンと交換
$client->fetchAccessTokenWithAuthCode($_GET['code'], $_SESSION['google_code_verifier']);
$_SESSION['google_access_token'] = $client->getAccessToken();

header('Location: ' . filter_var(GOOGLE_OAUTH_REDIRECT, FILTER_SANITIZE_URL));
exit;