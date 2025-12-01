<?php
declare(strict_types=1);

use Vmatch\Oauth\GoogleAuthorization;
use Vmatch\UserAuthentication\UserAuthentication;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start(['use_strict_mode' => 1]);

const GOOGLECALLBACK = 'googleCallback.php';
const PROFILESETTNG = '../NewUserRegistration/profileSetting.php';
const DASHBOARD = '../dashboard.php';

// アクセストークンがセッションにない場合、認証を開始
if (!isset($_SESSION['google_access_token']) || empty($_SESSION['google_access_token'])) {

    header('Location: ' . filter_var(GOOGLECALLBACK, FILTER_SANITIZE_URL));
    exit;

} else {

    $googleAuthorization = new GoogleAuthorization();
    $client = $googleAuthorization->clientConfig();

    $token = $client->verifyIdToken();
}

$userAuthentication = new UserAuthentication();

if ($userAuthentication->providerIdExists($token['sub'])) {

    // IDが存在する場合、ダッシュボードへリダイレクト
    header('Location:' . filter_var(DASHBOARD, FILTER_SANITIZE_URL));
    exit;
}

// IDが存在しない場合、プロフィール設定へリダイレクト
header('Location:' . filter_var(PROFILESETTNG, FILTER_SANITIZE_URL));
exit;