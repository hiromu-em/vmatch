<?php

use Google\Client;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start([
    'use_strict_mode' => 1
]);

$client = new Client();
$client->setAuthConfig(__DIR__ . '/../../../gmail_client_secret.json');
$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/src/php/oauth/googleCallback.php');
$client->addScope('https://www.googleapis.com/auth/userinfo.email');

$state = bin2hex(random_bytes(128 / 8));
$client->setState($state);
$client->setAccessType('offline');
$client->setIncludeGrantedScopes(true);

if (!isset($_GET['code'])) {

    $_SESSION['code_verifier'] = $client->getOAuth2Service()->generateCodeVerifier(); 
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit;

} else {

    $client->fetchAccessTokenWithAuthCode($_GET['code'], $_SESSION['code_verifier']);
    $_SESSION['access_token'] = $client->getAccessToken();
    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/src/php/oauth/googleOauth.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    exit;
}