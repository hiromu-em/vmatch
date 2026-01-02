<?php
declare(strict_types=1);

namespace Vmatch\Oauth;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

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
    public function setOauthToken(string $oauthToken = "", string $oauthTokenSecret = ""): void
    {
        $this->twitterOAuth->setOauthToken($oauthToken, $oauthTokenSecret);
    }

    /**
     * リクエストトークンを取得
     */
    public function getRequestToken(): array
    {
        return $this->twitterOAuth->oauth('oauth/request_token', [
            'oauth_callback' => self::Twitter_CALLBACK__LOCAL_URL
        ]);
    }

    /**
     * リクエストトークンとアクセストークンを交換
     * @param string $oauthVerifier OAuth検証子
     */
    public function exchangeAccessToken(string $oauthVerifier): array
    {
        return $this->twitterOAuth->oauth("oauth/access_token", [
            "oauth_verifier" => $oauthVerifier
        ]);
    }

    /**
     * 認可サーバーのURLを作成
     * @param string $oauthToken リクエストトークン
     */
    public function createAuthUrl(string $oauthToken): string
    {
        return $this->twitterOAuth->url('oauth/authorize', [
            'oauth_token' => $oauthToken
        ]);
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