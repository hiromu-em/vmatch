<?php
declare(strict_types=1);

use Vmatch\Oauth\TwitterAuthorization;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start([
    'use_strict_mode' => 1
]);

const X_OAUTH_PATH = 'xoauth.php';

// コールバックからのリクエストを処理
if (isset($_GET['oauth_token']) && $_SESSION['oauth_token'] !== $_GET['oauth_token']) {
    die('Error: OAuth token mismatch.');
}

$twitterAuthorization = new TwitterAuthorization();
$connection = $twitterAuthorization->createTwitterConnection($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

// アクセストークンを取得してセッションに保存
$_SESSION['access_token'] = $twitterAuthorization->exchangeAccessToken($connection);

header('Location:' . X_OAUTH_PATH);
exit;