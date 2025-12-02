<?php
declare(strict_types=1);

namespace Vmatch\Oauth;

use Abraham\TwitterOAuth\TwitterOAuth;
use Vmatch\Config;

require_once __DIR__ . '/../../../vendor/autoload.php';

class TwitterAuthorization
{
    private const string X_CALLBACK_LOCALHOST_URL = 'http://localhost:8080/src/php/Oauth/xoauthCallback.php';

    public function __construct()
    {
        // 環境変数の読み込み
        $config = new Config();
        $config->loadDotenvIfLocal();
    }

    /**
     * Twitter接続情報を作成する
     */
    public function createTwitterConnection(?string $token = null, ?string $secret = null): TwitterOAuth
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
}