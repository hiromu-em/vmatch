<?php
declare(strict_types=1);

use Vmatch\Oauth\GoogleAuthorization;
use Vmatch\Config;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start(['use_strict_mode' => 1]);

if (isset($_GET['error'])) {

    // アクセス拒否の処理
    header('Location: ' . filter_var('/', FILTER_SANITIZE_URL));
    exit;
}

$googleAuthorization = new GoogleAuthorization();

// セッションからstateパラメーターを設定
$googleAuthorization->setState($_SESSION['google_oauth_state'] ?? '');

try {
    // CSRF対策：stateを検証
    $googleAuthorization->verifyState($_GET['state'] ?? '');

} catch (\InvalidArgumentException $e) {

    unset($_SESSION['google_oauth_state'], $_SESSION['google_code_verifier']);
    $clearUrl = strtok($_SERVER['REQUEST_URI'], '?');

    http_response_code(400);

    // CSRF検出時のエラーページへリダイレクト
    header('Location: ' . filter_var('../error/oauthError.php', FILTER_SANITIZE_URL));
    exit;
}

$config = new Config();

// 環境変数の読み込み（ローカル環境のみ）
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$config->setHost($host);
$config->loadDotenvIfLocal();

// Google認証クラスのインスタンス化
$googleAuthorization = new GoogleAuthorization($config);
$client = $googleAuthorization->clientSetting();

//コードをアクセストークンと交換
if (isset($_GET['code'])) {

    $client->fetchAccessTokenWithAuthCode($_GET['code'], $_SESSION['google_code_verifier']);
    $_SESSION['google_access_token'] = $client->getAccessToken();

    unset($_SESSION['google_oauth_state'], $_SESSION['google_code_verifier']);

    header('Location: ' . filter_var('googleOauth.php', FILTER_SANITIZE_URL));
    exit;
}