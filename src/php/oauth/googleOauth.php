<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Google\Client;
use Google\Service\Oauth2;
use Vmatch\NewUserRegistration\UserRegistrationService;

session_start([
    'use_strict_mode' => 1
]);

//本番環境と開発環境の分岐
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'localhost') !== false) {

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../..");
    $dotenv->load();
}

const GOOGLECALLBACK = 'googleCallback.php';

// クライアント情報を設定
$client = new Client();
$client->setAuthConfig([
    'client_id' => $_ENV['CLIENTID'] ?? getenv('CLIENTID'),
    'client_secret' => $_ENV['CLIENTSECRET'] ?? getenv('CLIENTSECRET')
]);

$client->addScope(Oauth2::USERINFO_EMAIL);

// アクセストークンが存在すれば、メールアドレスを取得
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);

    $oauth = new Oauth2($client);
    $userInfo = $oauth->userinfo->get();

    $userRegistrationService = new UserRegistrationService();
    $emailExists = $userRegistrationService->emailExists($userInfo->email);
    $emailExists ? header("Location"): "";

} else {

    $redirect_uri = GOOGLECALLBACK;
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    exit;
}