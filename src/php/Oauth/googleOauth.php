<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ .'/../../../index.php';

use Google\Client;
use Google\Service\Oauth2;
use Vmatch\NewUserRegistration\UserRegistrationService;

session_start(['use_strict_mode' => 1]);

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

const GOOGLECALLBACK = 'googleCallback.php';
const PROFILESETTNG = '../NewUserRegistration/profileSetting.php';
const DASHBOARD = '../dashboard.php';

// .envファイル実行開始
loadDotenvIfLocal();

// クライアント情報を設定
$client = createGoogleClient();

// アクセストークンが無ければ認可フローへ進む
if (empty($_SESSION['google_access_token'])) {
    header('Location: ' . filter_var(GOOGLECALLBACK, FILTER_SANITIZE_URL));
    exit;
}

$client->addScope(Oauth2::USERINFO_EMAIL);
$client->setAccessToken($_SESSION['google_access_token']);

$oauth = new Oauth2($client);
$userInfo = $oauth->userinfo->get();
$userRegistrationService = new UserRegistrationService();

// メールアドレスの存在確認
$emailExists = $userRegistrationService->emailExists($userInfo->email);

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
$userRegistrationService->registerEmail($userInfo->email);
header("Location: $redirect_url");
exit;