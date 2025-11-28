<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Google\Client;
use Google\Service\Oauth2;

session_start(['use_strict_mode' => 1]);

const GOOGLE_CALLBACK_PATH = '/src/php/Oauth/googleCallback.php';
const GOOGLE_OAUTH_REDIRECT = 'googleOauth.php';

function loadDotenvIfLocal(): void
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, 'localhost') !== false) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../..");
        $dotenv->load();
    }
}

/**
 * Google クライアントを作成する<br>
 * $redirectUri を与えれば setRedirectUri を実行する
 */
function createGoogleClient(?string $redirectUri = null): Client
{
    $client = new Client();
    $client->setAuthConfig([
        'client_id' => $_ENV['CLIENTID'] ?? getenv('CLIENTID'),
        'client_secret' => $_ENV['CLIENTSECRET'] ?? getenv('CLIENTSECRET'),
    ]);
    if ($redirectUri) {
        $client->setRedirectUri($redirectUri);
    }
    $client->addScope(Oauth2::USERINFO_EMAIL);
    return $client;
}


loadDotenvIfLocal();

// スキーマは localhost なら http、それ以外は https
$host = $_SERVER['HTTP_HOST'] ?? '';
$urlSchema = (strpos($host, 'localhost') !== false) ? 'http://' : 'https://';
$redirectUri = $urlSchema . $host . GOOGLE_CALLBACK_PATH;

$client = createGoogleClient($redirectUri);

// 認可コードがない場合は認可はURLを生成してリダイレクト
if (!isset($_GET['code'])) {
    $state = bin2hex(random_bytes(128 / 8));
    $_SESSION['google_oauth_state'] = $state;
    $client->setState($state);

    // PKCE (Proof Key for Code Exchange) のためのコード検証者を生成し、セッションに保存
    $_SESSION['google_code_verifier'] = $client->getOAuth2Service()->generateCodeVerifier();

    $client->setAccessType('offline');
    $client->setIncludeGrantedScopes(true);

    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit;
}

// CSRF対策：stateを検証
if ($_GET['state'] !== $_SESSION['google_oauth_state']) {
    unset($_SESSION['google_oauth_state']);
    exit('Invalid state');
}

unset($_SESSION['google_oauth_state']);

//コードをアクセストークンと交換
$client->fetchAccessTokenWithAuthCode($_GET['code'], $_SESSION['google_code_verifier']);
$_SESSION['google_access_token'] = $client->getAccessToken();

header('Location: ' . filter_var(GOOGLE_OAUTH_REDIRECT, FILTER_SANITIZE_URL));
exit;