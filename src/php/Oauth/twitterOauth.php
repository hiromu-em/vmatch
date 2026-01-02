<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use Vmatch\Oauth\TwitterAuthorization;
use Vmatch\UserAuthentication\UserAuthentication;
use Vmatch\Config;

session_start(['use_strict_mode' => 1]);

const DASHBOARD = '../dashboard.php';
const PROFILESETTNG = '../UserAuthentication/profileSetting.php';
const SYSTEMERROR = '../error/systemError.php';

$config = new Config();

// 環境変数の読み込み（ローカル環境のみ）
$serverHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$config->setHost($serverHost);
$config->loadDotenvIfLocal();

$twitterAutho = new TwitterOAuth(
    $_ENV['TWITTER_API_KEY'] ?? getenv('TWITTER_API_KEY'),
    $_ENV['TWITTER_API_KEY_SECRET'] ?? getenv('TWITTER_API_KEY_SECRET')
);

$twitterAuthorization = new TwitterAuthorization($twitterAutho);

// APIバージョンを設定
$twitterAuthorization->setApiVersion();

if (isset($_SESSION['access_token']) || !empty($_SESSION['access_token'])) {

    $access_token = $_SESSION['access_token'];

    // アクセストークンを設定
    $twitterAuthorization->setOauthToken(
        $access_token['oauth_token'],
        $access_token['oauth_token_secret']
    );

    // ユーザー認証情報取得
    $user = $twitterAuthorization->getUserVerifyCredentials();

    if (empty($user) || !isset($user['id_str'])) {

        // エラーページへリダイレクト
        http_response_code(500);
        header('Location:' . filter_var(SYSTEMERROR, FILTER_SANITIZE_URL));
        exit;
    }

    // データベース接続の設定
    $databaseSettings = $config->getDatabaseSettings();

    try {
        $databaseConnection = new \PDO(
            $databaseSettings['dsn'],
            $databaseSettings['user'],
            $databaseSettings['password'],
            $databaseSettings['options']
        );
    } catch (PDOException $e) {

        // エラーページへリダイレクト
        http_response_code(500);
        header('Location:' . filter_var(SYSTEMERROR, FILTER_SANITIZE_URL));
        exit;
    }

    $userAuthentication = new UserAuthentication($databaseConnection);

    // プロバイダーIDの存在確認
    if ($userAuthentication->providerIdExists($user['id_str'])) {

        // IDが存在する場合、ダッシュボードへリダイレクト
        header('Location:' . filter_var(DASHBOARD, FILTER_SANITIZE_URL));
        exit;
    }

    try {
        // ユーザーのメールアドレスを登録
        $userAuthentication->registerEmail($user['email']);

        // 該当するユーザーのIDを検索する
        $userId = $userAuthentication->getSearchUserId($user['email']);

        // プロバイダ―IDとuserIDを紐付ける
        $userAuthentication->linkProviderUserId($userId, $user['id_str'], 'twitter');

    } catch (PDOException $e) {

        // エラーページへリダイレクト
        http_response_code(500);
        header('Location:' . filter_var(SYSTEMERROR, FILTER_SANITIZE_URL));
        exit;
    }

    // IDが存在しない場合、プロフィール設定へリダイレクト
    header('Location:' . filter_var(PROFILESETTNG, FILTER_SANITIZE_URL));
    exit;
}

// リクエストトークンを取得
$requestToken = $twitterAuthorization->getRequestToken();

// 認可サーバーのURLを作成
$authUrl = $twitterAuthorization->createAuthUrl($requestToken['oauth_token']);

$_SESSION['oauth_token'] = $requestToken['oauth_token'];
$_SESSION['oauth_token_secret'] = $requestToken['oauth_token_secret'];

// 認証サーバーにリダイレクト
header("Location: " . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;