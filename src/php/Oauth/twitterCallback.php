<?php
declare(strict_types=1);

use Vmatch\Oauth\TwitterAuthorization;
use Vmatch\Config;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start([
    'use_strict_mode' => 1
]);

// ユーザーが認可を拒否した処理
if (isset($_GET['denied'])) {

    header('Location: ' . filter_var('/', FILTER_SANITIZE_URL));
    exit;
}

// トークンの照合
if ($_SESSION['oauth_token'] !== $_GET['oauth_token']) {

    unset($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    $clearUrl = strtok($_SERVER['REQUEST_URI'], '?');

    // セッション情報やパラメータをクリアにして元のURLへリダイレクト
    header('Location: ' . filter_var($clearUrl, FILTER_SANITIZE_URL));
    exit;

} elseif (!isset($_GET['oauth_token'])) {

    // 不正なアクセス処理    
    http_response_code(401);
    include_once __DIR__ . '/../error/oauthError.php';
    exit;
}

$twitterAuthorization = new TwitterAuthorization();

$config = new Config();

// 環境変数の読み込み（ローカル環境のみ）
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$config->setHost($host);
$config->loadDotenvIfLocal();

// Twitterの接続情報を作成
$twitterAuthorization->createTwitterConnection($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

// アクセストークンを取得してセッションに保存
$_SESSION['access_token'] = $twitterAuthorization->exchangeAccessToken();

header('Location:' . filter_var('twitterOauth.php', FILTER_SANITIZE_URL));
exit;