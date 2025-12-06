<?php
declare(strict_types=1);

use Vmatch\Oauth\GoogleAuthorization;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start(['use_strict_mode' => 1]);

$googleAuthorization = new GoogleAuthorization();
$client = $googleAuthorization->clientConfig();

// CSRF対策：stateを検証
if (isset($_GET['state']) && $_GET['state'] !== $_SESSION['google_oauth_state']) {

    unset($_SESSION['google_oauth_state'], $_SESSION['google_code_verifier']);
    $clearUrl = strtok($_SERVER['REQUEST_URI'], '?');

    header('Location: ' . filter_var($clearUrl, FILTER_SANITIZE_URL));
    exit;

} elseif (!isset($_GET['state']) || !isset($_GET['code'])) {

    // 不正なアクセスの処理
    http_response_code(401);
    include_once __DIR__ . '/../error/oauthError.php';
    exit;
}

// アクセス拒否の処理
if (isset($_GET['error'])) {
    header('Location: ' . filter_var('/', FILTER_SANITIZE_URL));
    exit;
}

//コードをアクセストークンと交換
if (isset($_GET['code'])) {

    $client->fetchAccessTokenWithAuthCode($_GET['code'], $_SESSION['google_code_verifier']);
    $_SESSION['google_access_token'] = $client->getAccessToken();

    unset($_SESSION['google_oauth_state'], $_SESSION['google_code_verifier']);

    header('Location: ' . filter_var('googleOauth.php', FILTER_SANITIZE_URL));
    exit;
}