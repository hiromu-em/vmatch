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

// host名の取得
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Configクラスのインスタンス化 & dotenvの読み込み
$config = new Config($host);
$config->loadDotenvIfLocal();

// GoogleAuthorizationクラスのインスタンス化 & Google Clientの設定
$googleAuthorization = new GoogleAuthorization($config, new \Google\Client());
$client = $googleAuthorization->clientConfig($_SESSION['google_access_token'] ?? '');

// アクセストークンがSESSIONに存在しない場合、認証サーバーのURLを生成
if (!isset($_SESSION['google_access_token']) || empty($_SESSION['google_access_token'])) {

    $authUrl = $googleAuthorization->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}

// ユーザー情報を取得
$token = $client->verifyIdToken();

// データベース接続の設定
$databaseSettings = $config->getDatabaseSettings();
$databaseConnection = new \PDO(
    $databaseSettings['dsn'],
    $databaseSettings['user'],
    $databaseSettings['password'],
    $databaseSettings['options']
);

$userAuthentication = new UserAuthentication($databaseConnection);

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

    // ユーザーIDとプロパイダ―IDを紐付ける
    $userAuthentication->linkProviderUserId($userId, $token['sub'], 'google');

} catch (PDOException $e) {

    // エラーページへリダイレクト
    http_response_code(500);
    header('Location:' . filter_var(CONFIGERROR, FILTER_SANITIZE_URL));
    exit;
}

// プロフィール設定へリダイレクト
header('Location:' . filter_var(PROFILESETTNG, FILTER_SANITIZE_URL));
exit;