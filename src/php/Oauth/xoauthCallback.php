<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ .'/../../../index.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;

session_start([
    'use_strict_mode' => 1
]);

/**
 * Twitter の接続生成（セッション内oauth_tokenを使用）
 */
function createTwitterConnectionFromSession(): TwitterOAuth
{
    return new TwitterOAuth(
        $_ENV['X_APIKEY'] ?? getenv('X_APIKEY'),
        $_ENV['X_APIKEY_SECRET'] ?? getenv('X_APIKEY_SECRET'),
        $_SESSION['x_oauth_token'] ?? null,
        $_SESSION['x_oauth_token_secret'] ?? null
    );
}

// .envファイル実行開始
loadDotenvIfLocal();


// トークンチェック
if (isset($_GET['oauth_token']) && $_SESSION['x_oauth_token'] !== $_GET['oauth_token']) {
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

$_SESSION['x_access_token'] = $access_token;

header('Location: xoauth.php');
exit();