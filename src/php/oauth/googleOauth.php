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
const PROFILESETTNG = '../NewUserRegistration/profileSetting.php';
const DASHBOARD = '../dashboard.php';

// クライアント情報を設定
$client = new Client();
$client->setAuthConfig([
    'client_id' => $_ENV['CLIENTID'] ?? getenv('CLIENTID'),
    'client_secret' => $_ENV['CLIENTSECRET'] ?? getenv('CLIENTSECRET')
]);

// アクセストークンが無ければ認証に進む
if (empty($_SESSION['access_token'])) {

    header('Location: ' . filter_var(GOOGLECALLBACK, FILTER_SANITIZE_URL));
    exit;
}

$client->addScope(Oauth2::USERINFO_EMAIL);
$client->setAccessToken($_SESSION['access_token']);

$oauth = new Oauth2($client);
$userInfo = $oauth->userinfo->get();

$userRegistrationService = new UserRegistrationService();

// メールアドレスの確認
$emailExists = $userRegistrationService->emailExists($userInfo->email);

$redirect_dashboard = filter_var(DASHBOARD, FILTER_SANITIZE_URL);
$redirect_profile = filter_var(PROFILESETTNG, FILTER_SANITIZE_URL);

// リダイレクト先を変更
$redirect_url = $emailExists ? $redirect_dashboard : $redirect_profile;

if ($emailExists) {

    header("Location: $redirect_url");
    exit;

} else {

    $userRegistrationService->registerEmail($userInfo->email);
    header("Location: $redirect_url");
    exit;
}