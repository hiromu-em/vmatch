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
    private const string Twitter_CALLBACK__LOCAL_URL = 'http://localhost:8000/src/php/Oauth/twitterCallback.php';

    public function __construct(private ?TwitterOAuth $twitterOAuth = null)
    {
    }

    /**
     * APIバージョンを設定   
     */
    public function setApiVersion(): void
    {
        $this->twitterOAuth->setApiVersion('1.1');
    }

    /**
     * Oauthトークンを設定
     * @param string|null $oauthToken
     * @param string|null $oauthTokenSecret
     */
    public function setOauthToken(?string $oauthToken = null, ?string $oauthTokenSecret = null): void
    {
        $this->twitterOAuth->setOauthToken($oauthToken, $oauthTokenSecret);
    }

    /**
     * リクエストトークンを取得
     * @param TwitterOAuth $connection
     * @return array `request_token`
     */
    public function getRequestToken(): array
    {
        $request_token = $this->twitterOAuth->oauth('oauth/request_token', [
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
        $access_token = $this->twitterOAuth->oauth("oauth/access_token", [
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
        $auth_url = $this->twitterOAuth->url('oauth/authorize', [
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
        return get_object_vars($this->twitterOAuth->get("account/verify_credentials", [
            'include_email' => 'true',
            'skip_status' => 'true',
            'include_entities' => 'false'
        ]));
    }
}