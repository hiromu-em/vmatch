<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Vmatch\Oauth\GoogleAuthorization;
use Vmatch\UserAuthentication\UserAuthentication;
use Vmatch\Config;

session_start(['use_strict_mode' => 1]);

const PROFILESETTNG = '../lnitialProfileSettings.php';
const DASHBOARD = '../dashboard.php';
const SYSTEMERROR = '../error/systemError.php';

$config = new Config();

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$config->setHost($host);
$config->loadDotenvIfLocal();

$googleAuthorization = new GoogleAuthorization($config, new \Google\Client());
$googleAuthorization->setClient();

if (!isset($_SESSION['google_access_token']) || empty($_SESSION['google_access_token'])) {

    $state = $googleAuthorization->createState();
    $googleAuthorization->setClientState($state);

    $_SESSION['google_oauth_state'] = $state;
    $_SESSION['google_code_verifier'] = $googleAuthorization->generateCodeVerifier();

    $authUrl = $googleAuthorization->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}

$AccessToken = $_SESSION['google_access_token'] ?? [];

if (empty($AccessToken)) {
    http_response_code(500);
    header('Location:' . filter_var(SYSTEMERROR, FILTER_SANITIZE_URL));
    exit;
}

$googleAuthorization->setAccessToken($AccessToken);

$token = $googleAuthorization->getIdToken();

if (empty($token)) {
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

if ($userAuthentication->providerIdExists($token['sub'])) {
    header('Location:' . filter_var(DASHBOARD, FILTER_SANITIZE_URL));
    exit;
}

try {
    // 新規登録処理
    $userAuthentication->registerEmail($token['email']);
    $userId = $userAuthentication->getSearchUserId($token['email']);
    $userAuthentication->linkProviderUserId($userId, $token['sub'], 'google');

} catch (PDOException $e) {

    http_response_code(500);
    header('Location:' . filter_var(SYSTEMERROR, FILTER_SANITIZE_URL));
    exit;
}

header('Location:' . filter_var(PROFILESETTNG, FILTER_SANITIZE_URL));
exit;