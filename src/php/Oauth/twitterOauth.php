<?php
declare(strict_types=1);

use Vmatch\Oauth\TwitterAuthorization;
use Vmatch\UserAuthentication\UserAuthentication;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start([
    'use_strict_mode' => 1
]);

const DASHBOARD = '../dashboard.php';
const PROFILESETTNG = '../UserAuthentication/profileSetting.php';

$TwitterAuthorization = new TwitterAuthorization();

if (isset($_SESSION['access_token'])) {

    $access_token = $_SESSION['access_token'];

    $connection = $TwitterAuthorization->createTwitterConnection(
        $access_token['oauth_token'],
        $access_token['oauth_token_secret']
    );

    $user = get_object_vars($connection->get("account/verify_credentials", [
        'include_email' => 'true',
        'skip_status' => 'true',
        'include_entities' => 'false'
    ]));

    $userAuthentication = new UserAuthentication();
    if ($userAuthentication->providerIdExists($user['id_str'])) {

        // IDが存在する場合、ダッシュボードへリダイレクト
        header('Location:' . filter_var(DASHBOARD, FILTER_SANITIZE_URL));
        exit;
    }

    $userAuthentication->registerEmail($user['email']);

    $userId = $userAuthentication->userInfoSearch($user['email']);

    $userAuthentication->linkProviderUserId($userId, $user['id_str'], 'twitter');

    // IDが存在しない場合、プロフィール設定へリダイレクト
    header('Location:' . filter_var(PROFILESETTNG, FILTER_SANITIZE_URL));
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