<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;

session_start([
    'use_strict_mode' => 1
]);

const X_CALLBACK_LOCALHOST_URL = 'http://localhost:8080/src/php/Oauth/xoauthCallback.php';

/**
 * 開発環境なら.envファイルとlocalhost用のコールバックを読み込む
 */
function loadDotenvIfLocal()
{
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {

        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../..");
        $dotenv->load();
    }
}

/**
 * TwitterOAuthのインスタンスを作成する
 */
function createTwitterConnection(?string $token = null, ?string $secret = null): TwitterOAuth
{
    $connection = new TwitterOAuth(
        $_ENV['X_APIKEY'] ?? getenv('X_APIKEY'),
        $_ENV['X_APIKEY_SECRET'] ?? getenv('X_APIKEY_SECRET'),
        $token ?? null,
        $secret ?? null
    );

    $connection->setApiVersion('1.1');

    return $connection;
}

// .envファイル実行開始
loadDotenvIfLocal();

if (isset($_SESSION['x_access_token'])) {

    $access_token = $_SESSION['x_access_token'];

    try {
        $connection = createTwitterConnection(
            $access_token['oauth_token'],
            $access_token['oauth_token_secret']
        );

        $user = $connection->get("account/verify_credentials", [
            'include_email' => 'true',
            'skip_status' => 'true',
        ]);

    } catch (TwitterOAuthException $e) {

        echo 'Error: ' . $e->parsedMessage();
        exit();

    } catch (Exception $e) {

        echo 'Error: ' . $e->getMessage();
        exit();
    }

    exit;
}

try {
    // TwitterOAuthのインスタンスを作成
    $connection = createTwitterConnection();

    // 一時的なリクエストトークンを取得
    $request_token = $connection->oauth('oauth/request_token', [
        'oauth_callback' => (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) ? X_CALLBACK_LOCALHOST_URL : getenv('X_CALLBACK_URL')
    ]);

} catch (TwitterOAuthException $e) {
    echo 'Error: ' . $e->parsedMessage();
    exit();

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}

$_SESSION['x_oauth_token'] = $request_token['oauth_token'];
$_SESSION['x_oauth_token_secret'] = $request_token['oauth_token_secret'];

// 認証URLを取得してリダイレクト
$url = $connection->url('oauth/authorize', [
    'oauth_token' => $_SESSION['x_oauth_token']
]);

header("Location: $url");
exit();