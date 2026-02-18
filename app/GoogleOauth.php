<?php
declare(strict_types=1);

namespace Vmatch;

use Google\Client;

class GoogleOauth
{
    public function __construct(private Client $client)
    {
    }

    public function createClient(array $clientConfig): Client
    {
        $this->client->setAuthConfig($clientConfig);

        $this->client->setScopes('email');
        $this->client->setAccessType('offline');

        $this->client->setIncludeGrantedScopes(true);
        $this->client->setPrompt('select_account');
        $this->client->setState(bin2hex(random_bytes(128 / 8)));

        $this->client->setRedirectUri('http://localhost/google-oauth-callback');

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

