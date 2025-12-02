<?php
declare(strict_types=1);

namespace Vmatch\Oauth;

use Abraham\TwitterOAuth\TwitterOAuth;
use Vmatch\Config;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Twitter認証クラス
 */
class TwitterAuthorization
{
    private const string Twitter_CALLBACK__LOCAL_URL = 'http://localhost:8080/src/php/Oauth/twitterCallback.php';

    public function __construct()
    {
        // 環境変数の読み込み
        $config = new Config();
        $config->loadDotenvIfLocal();
    }

    /**
     * Twitter接続情報を作成する
     * @param string|null $oauthToken
     * @param string|null $oauthTokenSecret
     * @return TwitterOAuth TwitterOAuth接続情報
     */
    public function createTwitterConnection(?string $oauthToken = null, ?string $oauthTokenSecret = null): TwitterOAuth
    {
        $connection = new TwitterOAuth(
            $_ENV['TWITTER_APIKEY'] ?? getenv('TWITTER_APIKEY'),
            $_ENV['TWITTER_APIKEY_SECRET'] ?? getenv('TWITTER_APIKEY_SECRET'),
            $oauthToken ?? null,
            $oauthTokenSecret ?? null
        );

        $connection->setApiVersion('1.1');

        return $connection;
    }

    /**
     * リクエスト情報を作成する
     * @param TwitterOAuth $connection
     * @return array `request_token`
     */
    public function createRequestInfo(TwitterOAuth $connection): array
    {
        $request_token = $connection->oauth('oauth/request_token', [
            'oauth_callback' => (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) ?
                self::Twitter_CALLBACK__LOCAL_URL : getenv('TWITTER_CALLBACK_URL')
        ]);

        return $request_token;
    }

    /**
     * リクエストトークンとアクセストークンを交換する
     * @param TwitterOAuth $connection TwitterOAuth接続情報
     * @return array `access_token`
     */
    public function exchangeAccessToken(TwitterOAuth $connection): array
    {
        $access_token = $connection->oauth("oauth/access_token", [
            "oauth_verifier" => $_GET['oauth_verifier']
        ]);

        return $access_token;
    }
}