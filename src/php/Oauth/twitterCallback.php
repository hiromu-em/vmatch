<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use Vmatch\Oauth\TwitterAuthorization;
use Vmatch\Config;

session_start([
    'use_strict_mode' => 1
]);

// ユーザーが認可を拒否した処理
if (!isset($_REQUEST['oauth_token']) || empty($_REQUEST['oauth_token'])) {

    header('Location: ' . filter_var('/', FILTER_SANITIZE_URL));
    exit;
}

$ouauth_token = $_SESSION['oauth_token'] ?? '';

// トークンの照合
if ($ouauth_token !== $_GET['oauth_token']) {

    unset($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

    // エラーページへリダイレクト
    http_response_code(401);
    header('Location: ' . filter_var('../error/oauthError.php', FILTER_SANITIZE_URL));
    exit;
}

$config = new Config();

// 環境変数の読み込み（ローカル環境のみ）
$serverHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$config->setHost($serverHost);
$config->loadDotenvIfLocal();

$twitterAutho = new TwitterOAuth(
    $_ENV['TWITTER_API_KEY'] ?? getenv('TWITTER_API_KEY'),
    $_ENV['TWITTER_API_KEY_SECRET'] ?? getenv('TWITTER_API_KEY_SECRET'),
    $_SESSION['oauth_token'],
    $_SESSION['oauth_token_secret']
);

$twitterAuthorization = new TwitterAuthorization($twitterAutho);

$oauthVerifier = $_GET['oauth_verifier'] ?? '';

if (empty($oauthVerifier)) {

    // エラーページへリダイレクト
    http_response_code(400);
    header('Location:' . filter_var('../error/oauthError.php', FILTER_SANITIZE_URL));
    exit;
}

// アクセストークンを取得してセッションに保存
$_SESSION['access_token'] = $twitterAuthorization->exchangeAccessToken($oauthVerifier);

header('Location:' . filter_var('twitterOauth.php', FILTER_SANITIZE_URL));
exit;