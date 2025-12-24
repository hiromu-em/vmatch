<?php
declare(strict_types=1);

namespace Vmatch\Oauth;

use Abraham\TwitterOAuth\TwitterOAuth;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Twitter認可クラス
 */
class TwitterAuthorization
{
    private TwitterOAuth $connection;

    private const string Twitter_CALLBACK__LOCAL_URL = 'http://localhost:8080/src/php/Oauth/twitterCallback.php';

    /**
     * Twitter Oauth1.0aの接続情報を作成
     * @param string|null $oauthToken
     * @param string|null $oauthTokenSecret
     */
    public function createTwitterConnection(?string $oauthToken = null, ?string $oauthTokenSecret = null): void
    {
        $this->connection = new TwitterOAuth(
            $_ENV['TWITTER_API_KEY'] ?? getenv('TWITTER_API_KEY'),
            $_ENV['TWITTER_API_KEY_SECRET'] ?? getenv('TWITTER_API_KEY_SECRET'),
            $oauthToken ?? null,
            $oauthTokenSecret ?? null
        );

        $this->connection->setApiVersion('1.1');
    }

    /**
     * リクエストトークンを取得
     * @param TwitterOAuth $connection
     * @return array `request_token`
     */
    public function getRequestToken(): array
    {
        $request_token = $this->connection->oauth('oauth/request_token', [
            'oauth_callback' => (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) ?
                self::Twitter_CALLBACK__LOCAL_URL : getenv('TWITTER_CALLBACK_URL')
        ]);

        return $request_token;
    }

    /**
     * リクエストトークンとアクセストークンを交換
     * @param TwitterOAuth $connection TwitterOAuth接続情報
     * @return array `access_token`
     */
    public function exchangeAccessToken(): array
    {
        $access_token = $this->connection->oauth("oauth/access_token", [
            "oauth_verifier" => $_GET['oauth_verifier']
        ]);

        return $access_token;
    }

    /**
     * 認可サーバーのURLを作成
     * @return string 認可サーバーのURL
     */
    public function createAuthUrl(): string
    {
        $auth_url = $this->connection->url('oauth/authorize', [
            'oauth_token' => $_SESSION['oauth_token']
        ]);

        return $auth_url;
    }

    /**
     * ユーザーの認証情報を取得
     * @return array ユーザー認証情報
     */
    public function getUserVerifyCredentials(): array
    {
        $user = get_object_vars($this->connection->get("account/verify_credentials", [
            'include_email' => 'true',
            'skip_status' => 'true',
            'include_entities' => 'false'
        ]));

        return $user;
    }
}