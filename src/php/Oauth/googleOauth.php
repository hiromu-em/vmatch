<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Vmatch\Oauth\GoogleAuthorization;
use Vmatch\UserAuthentication\UserAuthentication;
use Vmatch\Config;

session_start(['use_strict_mode' => 1]);

const PROFILESETTNG = '../UserAuthentication/profileSetting.php';
const DASHBOARD = '../dashboard.php';
const SYSTEMERROR = '../error/systemError.php';

$config = new Config();

// 環境変数の読み込み（ローカル環境のみ）
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$config->setHost($host);
$config->loadDotenvIfLocal();

$googleAuthorization = new GoogleAuthorization($config, new \Google\Client());
$googleAuthorization->setClient();

if (!isset($_SESSION['google_access_token']) || empty($_SESSION['google_access_token'])) {

    // stateパラメーターを生成してSESSIONに保存
    $state = $googleAuthorization->createState();
    $_SESSION['google_oauth_state'] = $state;
    $googleAuthorization->setClientState($state);

    // コード検証者を生成してSESSIONに保存
    $_SESSION['google_code_verifier'] = $googleAuthorization->generateCodeVerifier();

    $authUrl = $googleAuthorization->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}

$AccessToken = $_SESSION['google_access_token'] ?? [];

if (empty($AccessToken)) {
    // エラーページへリダイレクト
    http_response_code(500);
    header('Location:' . filter_var(SYSTEMERROR, FILTER_SANITIZE_URL));
    exit;
}

$googleAuthorization->setAccessToken($AccessToken);

// IDトークンの取得
$token = $googleAuthorization->getIdToken();

if (empty($token)) {

    // エラーページへリダイレクト
    http_response_code(500);
    header('Location:' . filter_var(SYSTEMERROR, FILTER_SANITIZE_URL));
    exit;
}

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
    header('Location:' . filter_var(SYSTEMERROR, FILTER_SANITIZE_URL));
    exit;
}

// プロフィール設定へリダイレクト
header('Location:' . filter_var(PROFILESETTNG, FILTER_SANITIZE_URL));
exit;