<?php
declare(strict_types=1);

namespace Vmatch\Oauth;

use Google\Client;
use Vmatch\Config;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Google認可クラス
 */
class GoogleAuthorization implements GoogleAuthorizationInterface
{
    /** @var string $state stateパラメーター */
    private string $state = '';
    
    /**
     * @param Config|null $config Configオブジェクト
     * @param Client|null $client Google Clientオブジェクト
     */
    public function __construct(private ?Config $config = null, private ?Client $client = null)
    {
    }

    /**
     * Google Clientの設定
     * @param string $accessToken アクセストークン
     * @return Client Google Clientオブジェクト
     */
    public function clientSetting(string $accessToken = ''): Client
    {
        $this->client->setAuthConfig($this->config->getGoogleClientEnvVars());

        $this->client->setScopes('email');
        $this->client->setAccessToken($accessToken);

        $this->client->setAccessType('offline');
        $this->client->setIncludeGrantedScopes(true);
        $this->client->setPrompt('select_account');

        $redirectUri = $this->config->urlScheme() . $this->config->getHost() . self::GOOGLE_CALLBACK;
        $this->client->setRedirectUri($redirectUri);

        return $this->client;
    }
    /**
     * 認可サーバーのURLを生成
     * @param string $state stateパラメーター
     * @return string 認可サーバーのURL
     */
    public function createAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * stateパラメーターの生成
     * @return string 生成されたstateパラメーター
     */
    public function createState(): string
    {
        return bin2hex(random_bytes(128 / 8));
    }

    /**
     * Clientにstateパラメーターを設定
     * @param string $state stateパラメーター
     * @return void
     */
    public function setClientState(string $state): void
    {
        $this->client->setState($state);
    }

    /**
     * stateパラメーターの取得
     * @return string stateパラメーター
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * stateパラメーターの取得
     * @return string stateパラメーター
     */   
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * コード検証者の取得
     * @return string コード検証者
     */
    public function generateCodeVerifier(): string
    {
        return $this->client->getOAuth2Service()->generateCodeVerifier();
    }

    /**
     * stateパラメーターの検証
     * @param string $state stateパラメーター
     * @return bool 検証結果
     */
    public function verifyState(string $state): void
    {
        if ($state !== $this->getState()) {
            throw new \InvalidArgumentException();
        }
    }
}

