<?php
declare(strict_types=1);

use Vmatch\Oauth\TwitterAuthorization;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start([
    'use_strict_mode' => 1
]);

$TwitterAuthorization = new TwitterAuthorization();

if (isset($_SESSION['access_token'])) {

    $access_token = $_SESSION['access_token'];

    $connection = $TwitterAuthorization->createTwitterConnection(
        $access_token['oauth_token'],
        $access_token['oauth_token_secret']
    );

    $user = $connection->get("account/verify_credentials", [
        'include_email' => 'true',
        'skip_status' => 'true',
        'include_entities' => 'false'
    ]);

    exit;
}

$connection = $TwitterAuthorization->createTwitterConnection();

// リクエストトークンを取得
$requestToken = $TwitterAuthorization->createRequestInfo($connection);

$_SESSION['oauth_token'] = $requestToken['oauth_token'];
$_SESSION['oauth_token_secret'] = $requestToken['oauth_token_secret'];

// 認証URLへリダイレクト
$url = $connection->url('oauth/authorize', [
    'oauth_token' => $_SESSION['oauth_token']
]);

header("Location: $url");
exit;