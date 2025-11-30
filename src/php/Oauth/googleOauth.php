<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../index.php';

use Google\Client;
use Google\Service\Oauth2;
use Vmatch\UserAuthentication\UserAuthentication;
use Vmatch\Config;

session_start(['use_strict_mode' => 1]);

const GOOGLECALLBACK = 'googleCallback.php';
const PROFILESETTNG = '../NewUserRegistration/profileSetting.php';
const DASHBOARD = '../dashboard.php';

/**
 * Google クライアントを作成する
 */
function createGoogleClient(): Client
{
    $client = new Client();
    $client->setAuthConfig([
        'client_id' => $_ENV['CLIENTID'] ?? getenv('CLIENTID'),
        'client_secret' => $_ENV['CLIENTSECRET'] ?? getenv('CLIENTSECRET'),
    ]);
    return $client;
}

$config = new Config();
$config->loadDotenvIfLocal();

// アクセストークンが無ければ認可フローに進む
if (!isset($_SESSION['google_access_token']) || empty($_SESSION['google_access_token'])) {
    header('Location: ' . filter_var(GOOGLECALLBACK, FILTER_SANITIZE_URL));
    exit;
}

$client = createGoogleClient();
$client->setAccessToken($_SESSION['google_access_token']);

// IDトークンの検証
$token = $client->verifyIdToken();

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