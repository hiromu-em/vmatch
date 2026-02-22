<?php
declare(strict_types=1);

namespace Vmatch;

use Google\Client;

class GoogleOauth
{
    private string $redirectUri = 'http://localhost/google-oauth-code';

    public function __construct(private Client $client)
    {
    }

    public function changeClientSetting(array $config): Client
    {
        $this->client->setAuthConfig($config);

        $this->client->setScopes('email');
        $this->client->setAccessType('offline');

        $this->client->setIncludeGrantedScopes(true);
        $this->client->setPrompt('select_account');

        $this->client->setRedirectUri($this->getRedirectUri());

        return $this->client;
    }

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

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

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

