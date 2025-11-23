<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Google\Client;
use Google\Service\Oauth2;

session_start([
    'use_strict_mode' => 1
]);

$client = new Client();
$client->setAuthConfig(__DIR__ . '/gmail_client_secret.json');
$client->addScope(Oauth2::USERINFO_EMAIL);

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);

    $oauth = new Oauth2($client);
    $userInfo = $oauth->userinfo->get();

    var_dump($userInfo);
} else {

    $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . '/src/php/oauth/googleCallback.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    exit;
}