<?php
declare(strict_types=1);

namespace Vmatch\Oauth;

use Google\Client;
use Vmatch\Config;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Google認可クラス
 */
class GoogleAuthorization
{
    private const string GOOGLE_CALLBACK = '/src/php/Oauth/googleCallback.php';

    /**
     * コンストラクタ<br>
     * @param Config|null $config Configオブジェクト
     * @param Client|null $client Google Clientオブジェクト
     */
    public function __construct(private ?Config $config = null, private ?Client $client = null)
    {
    }

    /**
     * Google Clientの設定<br>
     * @param string $accessToken アクセストークン
     * @return Client Google Clientオブジェクト
     */
    public function clientConfig(string $accessToken = ''): Client
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
     * 認可サーバーのURLを生成<br>
     * @return string 認可サーバーのURL
     */
    public function createAuthUrl(): string
    {
        $state = bin2hex(random_bytes(128 / 8));
        $_SESSION['google_oauth_state'] = $state;
        $this->client->setState($state);

        // PKCE (Proof Key for Code Exchange) のためのコード検証者を生成し、セッションに保存
        $_SESSION['google_code_verifier'] = $this->client->getOAuth2Service()->generateCodeVerifier();
        $auth_url = $this->client->createAuthUrl();

        return $auth_url;
    }
}

