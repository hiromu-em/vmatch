<?php
declare(strict_types=1);

use Vmatch\Oauth\GoogleAuthorization;
use Google\Service\Oauth2;
use Vmatch\UserAuthentication\UserAuthentication;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start(['use_strict_mode' => 1]);

const GOOGLECALLBACK = 'googleCallback.php';
const PROFILESETTNG = '../NewUserRegistration/profileSetting.php';
const DASHBOARD = '../dashboard.php';

$googleAuthorization = new GoogleAuthorization();
$client = $googleAuthorization->clientConfig();

// アクセストークンがセッションにない場合、認証を開始
if (!isset($_SESSION['google_access_token']) || empty($_SESSION['google_access_token'])) {
    header('Location: ' . filter_var(GOOGLECALLBACK, FILTER_SANITIZE_URL));
    exit;
}

// IDトークンを検証
if ($client->getAccessToken()) {

    $token = $client->verifyIdToken();
}

$oauth = new Oauth2($client);
$userInfo = $oauth->userinfo->get();
$userAuthentication = new UserAuthentication();

// メールアドレスの存在確認
$emailExists = $userAuthentication->emailExists($userInfo->email);

// リダイレクト先を変更
$redirect_dashboard = filter_var(DASHBOARD, FILTER_SANITIZE_URL);
$redirect_profile = filter_var(PROFILESETTNG, FILTER_SANITIZE_URL);
$redirect_url = $emailExists ? $redirect_dashboard : $redirect_profile;

if ($emailExists) {
    // メールアドレスが存在する場合、ダッシュボードへリダイレクト
    header("Location: $redirect_url");
    exit;
}

// メールアドレスが存在しない場合、プロフィール設定へリダイレクト
$userAuthentication->registerEmail($userInfo->email);
header("Location: $redirect_url");
exit;