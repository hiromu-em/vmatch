<?php
declare(strict_types=1);

use Vmatch\Oauth\GoogleAuthorization;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start(['use_strict_mode' => 1]);

$googleAuthorization = new GoogleAuthorization();
$client = $googleAuthorization->clientConfig();

// CSRF対策：stateを検証
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['google_oauth_state']) {

    unset($_SESSION['google_oauth_state'], $_SESSION['google_code_verifier']);
    http_response_code(401);

    include __DIR__ . '/../../php/error/oauthError.php';
    exit;
}

// アクセス拒否の処理
if ($_GET['error'] === 'access_denied') {
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