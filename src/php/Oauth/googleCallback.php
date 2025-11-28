<?php

use Google\Client;
use Google\Service\Oauth2;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start([
    'use_strict_mode' => 1
]);

const GOOGLECALLBACK = 'googleCallback.php';
const GOOGLEOAUTH = 'googleOauth.php';

/**
 * ホスト名に 'localhost' が含まれる場合は開発環境とみなし、.env ファイルを読み込む。
 * また、その場合は $urlSchema を 'http://' に設定し、それ以外は 'https://' を使用する。
 */
$host = $_SERVER['HTTP_HOST'];
$urlSchema = 'https://';
if (strpos($host, 'localhost') !== false) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../..");
    $dotenv->load();

    $urlSchema = 'http://';
}

// Google APIクライアントの初期化
$client = new Client();
$client->setAuthConfig([
    'client_id' => $_ENV['CLIENTID'] ?? getenv('CLIENTID'),
    'client_secret' => $_ENV['CLIENTSECRET'] ?? getenv('CLIENTSECRET')
]);

$client->setRedirectUri($urlSchema . $_SERVER['HTTP_HOST'] . GOOGLECALLBACK);
$client->addScope(Oauth2::USERINFO_EMAIL);

// 認可コードがない場合：Googleの認証ページへリダイレクト
if (!isset($_GET['code'])) {
    $state = bin2hex(random_bytes(128 / 8));
    $_SESSION['google_oauth_state'] = $state;
    $client->setState($state);

    // PKCE (Proof Key for Code Exchange) のためのコード検証者を生成し、セッションに保存
    $_SESSION['google_code_verifier'] = $client->getOAuth2Service()->generateCodeVerifier();

    $client->setAccessType('offline');
    $client->setIncludeGrantedScopes(true);

    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit;
}

// CSRF対策：stateを検証
if ($_GET['state'] !== $_SESSION['google_oauth_state']) {
    unset($_SESSION['google_oauth_state']);
    exit('Invalid state');
}

unset($_SESSION['google_oauth_state']);

//コードをアクセストークンと交換
$client->fetchAccessTokenWithAuthCode($_GET['code'], $_SESSION['google_code_verifier']);
$_SESSION['google_access_token'] = $client->getAccessToken();

$redirect_uri = GOOGLEOAUTH;
header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
exit;