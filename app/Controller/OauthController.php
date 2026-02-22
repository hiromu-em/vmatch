<?php
declare(strict_types=1);

namespace Controller;

use Core\Request;
use Core\Response;
use Core\Session;
use Vmatch\GoogleOauth;

class OauthController
{
    public function __construct(
        private Request $request,
        private Response $response,
        private Session $session
    ) {

    }
    /**
     * 
     * @param GoogleOauth $googleOauth GoogleOauthに関わる処理をまとめたオブジェクト
     * @param array $clientConfig クライアントIDとクライアントシークレットを含めた配列
     */
    public function handleGoogleOauth(GoogleOauth $googleOauth, array $clientConfig)
    {
        $client = $googleOauth->changeClientSetting($clientConfig);

        $googleAccessToken = $this->session->getStr('google_access_token');
        if (!isset($googleAccessToken) || empty($googleAccessToken)) {

            $state = bin2hex(random_bytes(128 / 8));
            $client->setState($state);

            $this->session->setStr('google_oauth_state', $state);
            $this->session->setStr('google_code_verifier', $client->getOAuth2Service()->generateCodeVerifier());

            $this->response->redirect($client->createAuthUrl(), 301);
        }

    }

    public function handleGoogleOauthCode(GoogleOauth $googleOauth)
    {
        if ($this->request->isGet('error')) {
            $this->response->redirect('/', 301);
        }

        $state = $this->request->fetchInputStr('state');
        $code = $this->request->fetchInputStr('code');
        $googleCodeVerifier = $this->session->getStr('google_code_verifier');

        if ($this->request->isGet('code')) {

            if ($state !== $this->session->getStr('google_oauth_state')) {
                $this->session->clear();
                $this->response->redirect('/', 301);
            }

            $client = $googleOauth->getGoogleClient();
            $riedirectUri = $googleOauth->getRedirectUri();

            $client->setRedirectUri($riedirectUri);
            $accessToken = $googleOauth->fetchAccessToken($code, $googleCodeVerifier);
        }
    }
}