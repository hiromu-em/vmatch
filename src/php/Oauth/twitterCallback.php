<?php
declare(strict_types=1);

use Vmatch\Oauth\TwitterAuthorization;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start([
    'use_strict_mode' => 1
]);

// コールバックからのリクエストを処理
if (isset($_GET['oauth_token']) && $_SESSION['oauth_token'] !== $_GET['oauth_token']) {
    die('Error: OAuth token mismatch.');
}

$twitterAuthorization = new TwitterAuthorization();
$twitterAuthorization->createTwitterConnection($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

// アクセストークンを取得してセッションに保存
$_SESSION['access_token'] = $twitterAuthorization->exchangeAccessToken();

header('Location:' . filter_var('twitterOauth.php', FILTER_SANITIZE_URL));
exit;