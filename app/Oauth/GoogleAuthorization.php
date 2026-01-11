<?php
declare(strict_types=1);

namespace Vmatch\Oauth;

use Google\Client;
use Vmatch\ConfigInterface;

/**
 * Google認可クラス
 */
class GoogleAuthorization
{
    private const string GOOGLE_CALLBACK = '/app/Oauth/googleCallback.php';

    /**
     * @param ConfigInterface|null $config
     * @param Client|null $client 
     */
    public function __construct(private ?ConfigInterface $config = null, private ?Client $client = null)
    {
    }

    /**
     * Clientの設定
     */
    public function setClient(): Client
    {
        $this->client->setAuthConfig($this->config->getGoogleClientEnvVars());
        $this->client->setScopes('email');

        $this->client->setAccessType('offline');
        $this->client->setIncludeGrantedScopes(true);
        $this->client->setPrompt('select_account');

        $redirectUri = $this->config->urlScheme() . $this->config->getHost() . self::GOOGLE_CALLBACK;
        $this->client->setRedirectUri($redirectUri);

        return $this->client;
    }

    /**
     * アクセストークンの設定
     */
    public function setAccessToken(array $accessToken): void
    {
        $this->client->setAccessToken($accessToken);
    }

    /**
     * 認可サーバーのURLを生成
     * @return string 認可サーバーのURL
     */
    public function createAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Clientにstateを設定
     */
    public function setClientState(string $state): void
    {
        $this->client->setState($state);
    }

    /**
     * コード検証者の取得
     */
    public function generateCodeVerifier(): string
    {
        return $this->client->getOAuth2Service()->generateCodeVerifier();
    }

    /**
     * 認可コードをアクセストークンと交換
     * @param string $code 認可コード
     * @param string $codeVerifier コード検証者
     */
    public function fetchAccessToken(string $code, string $codeVerifier): void
    {
        $this->client->fetchAccessTokenWithAuthCode($code, $codeVerifier);
    }

    /**
     * アクセストークンの取得
     */
    public function getAccessToken(): array
    {
        return $this->client->getAccessToken();
    }

    /**
     * IDトークンの取得
     */
    public function getIdToken(): array|false
    {
        return $this->client->verifyIdToken();
    }
}

