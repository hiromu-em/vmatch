<?php
declare(strict_types=1);

use Vmatch\Oauth\GoogleAuthorization;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start(['use_strict_mode' => 1]);

$googleAuthorization = new GoogleAuthorization();
$client = $googleAuthorization->clientConfig();

// CSRF対策：stateを検証
if ($_GET['state'] !== $_SESSION['google_oauth_state']) {

    unset($_SESSION['google_oauth_state']);
    exit('Invalid state');
}

//コードをアクセストークンと交換
if (isset($_GET['code'])) {

    unset($_SESSION['google_oauth_state']);

    $client->fetchAccessTokenWithAuthCode($_GET['code'], $_SESSION['google_code_verifier']);
    $_SESSION['google_access_token'] = $client->getAccessToken();

    header('Location: ' . filter_var('googleOauth.php', FILTER_SANITIZE_URL));
    exit;
}