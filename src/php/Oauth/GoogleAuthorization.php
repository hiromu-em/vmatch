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

    private Client $client;

    public function __construct(private ?Config $config = null)
    {
    }

    /**
     * GoogleClientの設定<br>
     * @return Client Google Clientオブジェクト
     */
    public function clientConfig(): Client
    {
        $this->client = new Client();
        $this->client->setAuthConfig([
            'client_id' => $_ENV['CLIENTID'] ?? getenv('CLIENTID'),
            'client_secret' => $_ENV['CLIENTSECRET'] ?? getenv('CLIENTSECRET'),
        ]);

        $this->client->setScopes('email');
        $this->client->setAccessToken($_SESSION['google_access_token'] ?? '');

        $this->client->setAccessType('offline');
        $this->client->setIncludeGrantedScopes(true);
        $this->client->setPrompt('select_account');

        $redirectUri = $this->config->urlScheme() . $_SERVER['HTTP_HOST'] . self::GOOGLE_CALLBACK;
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

