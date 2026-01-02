<?php
declare(strict_types=1);

use Vmatch\Oauth\TwitterAuthorization;
use PHPUnit\Framework\TestCase;
use Abraham\TwitterOAuth\TwitterOAuth;

require_once __DIR__ . '/../vendor/autoload.php';

class TwitterAuthorizationTest extends TestCase
{
    /**
     * setApiVersionメソッドのテスト
     */
    public function testSetApiVersion(): void
    {
        $twitterOAuthMock = $this->createMock(TwitterOAuth::class);

        $twitterOAuthMock
            ->expects($this->once())
            ->method('setApiVersion')
            ->with('1.1');

        $twitterAuthorization = new TwitterAuthorization($twitterOAuthMock);
        $twitterAuthorization->setApiVersion();

        $this->assertTrue(true);
    }

    /**
     * setOauthTokenメソッドのテスト（パラメーター指定なし）
     */
    public function testSetOauthTokenWithoutParameters(): void
    {
        $twitterOAuthMock = $this->createMock(TwitterOAuth::class);

        $twitterOAuthMock
            ->expects($this->once())
            ->method('setOauthToken')
            ->with("", "");

        $twitterAuthorization = new TwitterAuthorization($twitterOAuthMock);
        $twitterAuthorization->setOauthToken("", "");

        $this->assertTrue(true);
    }

    /**
     * setOauthTokenメソッドのテスト（パラメーター指定あり）
     */
    public function testSetOauthTokenWithParameters(): void
    {
        $twitterOAuthMock = $this->createMock(TwitterOAuth::class);

        $oauthToken = 'test_oauth_token';
        $oauthTokenSecret = 'test_oauth_token_secret';

        $twitterOAuthMock
            ->expects($this->once())
            ->method('setOauthToken')
            ->with($oauthToken, $oauthTokenSecret);

        $twitterAuthorization = new TwitterAuthorization($twitterOAuthMock);
        $twitterAuthorization->setOauthToken($oauthToken, $oauthTokenSecret);

        $this->assertTrue(true);
    }

    /**
     * getRequestTokenメソッドのテスト
     */
    public function testGetRequestToken(): void
    {
        $twitterOAuthMock = $this->createMock(TwitterOAuth::class);

        $expectedToken = [
            'oauth_token' => 'test_request_token',
            'oauth_token_secret' => 'test_request_token_secret',
            'oauth_callback_confirmed' => true
        ];

        $twitterOAuthMock
            ->expects($this->once())
            ->method('oauth')
            ->with('oauth/request_token', [
                'oauth_callback' => 'http://localhost:8000/src/php/Oauth/twitterCallback.php'
            ])
            ->willReturn($expectedToken);

        $twitterAuthorization = new TwitterAuthorization($twitterOAuthMock);
        $result = $twitterAuthorization->getRequestToken();

        $this->assertSame($expectedToken, $result);
    }

    /**
     * exchangeAccessTokenメソッドのテスト
     */
    public function testExchangeAccessToken(): void
    {
        $twitterOAuthMock = $this->createMock(TwitterOAuth::class);

        $oauthVerifier = 'test_oauth_verifier';
        $expectedToken = [
            'oauth_token' => 'test_access_token',
            'oauth_token_secret' => 'test_access_token_secret',
            'user_id' => '12345',
            'screen_name' => 'testuser'
        ];

        $twitterOAuthMock
            ->expects($this->once())
            ->method('oauth')
            ->with('oauth/access_token', [
                'oauth_verifier' => $oauthVerifier
            ])
            ->willReturn($expectedToken);

        $twitterAuthorization = new TwitterAuthorization($twitterOAuthMock);
        $result = $twitterAuthorization->exchangeAccessToken($oauthVerifier);

        $this->assertSame($expectedToken, $result);
    }

    /**
     * createAuthUrlメソッドのテスト
     */
    public function testCreateAuthUrl(): void
    {
        $twitterOAuthMock = $this->createMock(TwitterOAuth::class);

        $oauthToken = 'test_request_token';
        $expectedUrl = 'https://api.twitter.com/oauth/authorize?oauth_token=test_request_token';

        $twitterOAuthMock
            ->expects($this->once())
            ->method('url')
            ->with('oauth/authorize', [
                'oauth_token' => $oauthToken
            ])
            ->willReturn($expectedUrl);

        $twitterAuthorization = new TwitterAuthorization($twitterOAuthMock);
        $result = $twitterAuthorization->createAuthUrl($oauthToken);

        $this->assertSame($expectedUrl, $result);
    }

    /**
     * getUserVerifyCredentialsメソッドのテスト
     */
    public function testGetUserVerifyCredentials(): void
    {
        $twitterOAuthMock = $this->createMock(TwitterOAuth::class);

        $userData = new \stdClass();
        $userData->id = 12345;
        $userData->screen_name = 'testuser';
        $userData->name = 'Test User';
        $userData->email = 'test@example.com';
        $userData->profile_image_url_https = 'https://example.com/profile.jpg';

        $twitterOAuthMock
            ->expects($this->once())
            ->method('get')
            ->with('account/verify_credentials', [
                'include_email' => 'true',
                'skip_status' => 'true',
                'include_entities' => 'false'
            ])
            ->willReturn($userData);

        $twitterAuthorization = new TwitterAuthorization($twitterOAuthMock);
        $result = $twitterAuthorization->getUserVerifyCredentials();

        $this->assertIsArray($result);
        $this->assertEquals(12345, $result['id']);
        $this->assertEquals('testuser', $result['screen_name']);
        $this->assertEquals('Test User', $result['name']);
        $this->assertEquals('test@example.com', $result['email']);
        $this->assertEquals('https://example.com/profile.jpg', $result['profile_image_url_https']);
    }
}