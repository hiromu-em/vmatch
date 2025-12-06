<?php
declare(strict_types=1);

use Vmatch\Oauth\TwitterAuthorization;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start([
    'use_strict_mode' => 1
]);

// トークンの照合
if (isset($_GET['oauth_token']) && $_SESSION['oauth_token'] !== $_GET['oauth_token']) {

    unset($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    $clearUrl = strtok($_SERVER['REQUEST_URI'], '?');

    header('Location: ' . filter_var($clearUrl, FILTER_SANITIZE_URL));
    exit;

} elseif (!isset($_GET['oauth_token']) || !isset($_GET['oauth_verifier'])) {

    // 不正なアクセスの処理
    include_once __DIR__ . '/../error/oauthError.php';
    exit;
}

// アクセス拒否の処理
if (isset($_GET['denied'])) {
    header('Location: ' . filter_var('/', FILTER_SANITIZE_URL));
    exit;
}

$twitterAuthorization = new TwitterAuthorization();
$twitterAuthorization->createTwitterConnection($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

// アクセストークンを取得してセッションに保存
$_SESSION['access_token'] = $twitterAuthorization->exchangeAccessToken();

header('Location:' . filter_var('twitterOauth.php', FILTER_SANITIZE_URL));
exit;