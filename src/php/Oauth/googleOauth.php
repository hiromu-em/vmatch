<?php
declare(strict_types=1);

use Vmatch\Oauth\GoogleAuthorization;
use Vmatch\UserAuthentication\UserAuthentication;
use Vmatch\Config;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start(['use_strict_mode' => 1]);

const PROFILESETTNG = '../UserAuthentication/profileSetting.php';
const DASHBOARD = '../dashboard.php';
const CONFIGERROR = '../error/configError.php';

$googleAuthorization = new GoogleAuthorization();
$client = $googleAuthorization->clientConfig();

// アクセストークンがSESSIONに存在しない場合、認証サーバーのURLを生成
if (!isset($_SESSION['google_access_token']) || empty($_SESSION['google_access_token'])) {

    $authUrl = $googleAuthorization->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}

// ユーザー情報を取得
$token = $client->verifyIdToken();

// データベース接続の取得
$databaseConfig = new Config();

$userAuthentication = new UserAuthentication($databaseConfig->databaseConnection());

// IDが存在する場合、ダッシュボードへリダイレクト
if ($userAuthentication->providerIdExists($token['sub'])) {
    header('Location:' . filter_var(DASHBOARD, FILTER_SANITIZE_URL));
    exit;
}

try {
    // ユーザーのメールアドレスを登録
    $userAuthentication->registerEmail($token['email']);

    // 該当するユーザーのIDを検索する
    $userId = $userAuthentication->getSearchUserId($token['email']);

    // ユーザーIDとプロパイダ―IDの紐付け
    $userAuthentication->linkProviderUserId($userId, $token['sub'], 'google');

} catch (PDOException $e) {
    http_response_code(500);
    header('Location:' . filter_var(CONFIGERROR, FILTER_SANITIZE_URL));
    exit;
}

// プロフィール設定へリダイレクト
header('Location:' . filter_var(PROFILESETTNG, FILTER_SANITIZE_URL));
exit;