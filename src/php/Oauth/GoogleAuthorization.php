<?php
declare(strict_types=1);

namespace Vmatch\Oauth;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Google\Client;
use Vmatch\ConfigInterface;

/**
 * Google認可クラス
 */
class GoogleAuthorization
{
    /** @var string $state stateパラメーター */
    private string $state = '';

    /** @var string GOOGLE_CALLBACK コールバックURL */
    private const string GOOGLE_CALLBACK = '/src/php/Oauth/googleCallback.php';

    /**
     * @param ConfigInterface|null $config 設定オブジェクト
     * @param Client|null $client Google Clientオブジェクト
     */
    public function __construct(private ?ConfigInterface $config = null, private ?Client $client = null)
    {
    }

    /**
     * Google Clientの設定
     * @return Client Google Clientオブジェクト
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
     * stateの生成
     */
    public function createState(): string
    {
        return bin2hex(random_bytes(128 / 8));
    }

    /**
     * Clientにstateを設定
     */
    public function setClientState(string $state): void
    {
        $this->client->setState($state);
    }

    /**
     * stateの設定
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * stateの取得
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * コード検証者の取得
     */
    public function generateCodeVerifier(): string
    {
        return $this->client->getOAuth2Service()->generateCodeVerifier();
    }

    /**
     * stateの検証
     * @return bool 検証結果
     */
    public function verifyState(string $state): void
    {
        if ($state !== $this->getState()) {
            throw new \InvalidArgumentException();
        }
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

