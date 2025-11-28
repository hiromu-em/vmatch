<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;

session_start([
    'use_strict_mode' => 1
]);

/**
 * 開発環境なら.envファイルを読み込む
 */
function loadDotenvIfLocal(): void
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, 'localhost') !== false) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../..");
        $dotenv->load();
    }
}

/**
 * Twitter の接続生成（セッション内 request token を使用）
 */
function createTwitterConnectionFromSession(): TwitterOAuth
{
    return new TwitterOAuth(
        $_ENV['X_APIKEY'] ?? getenv('X_APIKEY'),
        $_ENV['X_APIKEY_SECRET'] ?? getenv('X_APIKEY_SECRET'),
        $_SESSION['oauth_token'] ?? null,
        $_SESSION['oauth_token_secret'] ?? null
    );
}

// .envファイル実行開始
loadDotenvIfLocal();


// トークンチェック
if (isset($_GET['oauth_token']) && $_SESSION['oauth_token'] !== $_GET['oauth_token']) {
    die('Error: OAuth token mismatch.');
}

try {
    // 一時的なリクエストトークンを使用してTwitterOAuthのインスタンスを作成
    $connection = createTwitterConnectionFromSession();

    $access_token = $connection->oauth("oauth/access_token", [
        "oauth_verifier" => $_GET['oauth_verifier']
    ]);

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit();

} catch (TwitterOAuthException $e) {
    echo 'Error: ' . $e->parsedMessage();
    exit();
}

$_SESSION['access_token'] = $access_token;

header('Location: xoauth.php');
exit();