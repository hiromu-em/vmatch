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
     * @param string $code 認可コード
     * @param string $codeVerifier PKCEに使用するコード検証子
     */
    public function fetchAccessToken(string $code, string $codeVerifier): array
    {
        return $this->client->fetchAccessTokenWithAuthCode($code, $codeVerifier);
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

