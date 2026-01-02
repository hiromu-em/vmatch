<?php
declare(strict_types=1);

use Vmatch\Oauth\GoogleAuthorization;
use PHPUnit\Framework\TestCase;
use Google\Client;
use Vmatch\Config;

require_once __DIR__ . '/../vendor/autoload.php';

class GoogleAuthorizationTest extends TestCase
{
    /**
     * ローカル環境でのGoogle Client設定テスト
     */
    public function testLocalSetClient(): void
    {
        $configMock = $this->createMock(Config::class);

        $configMock
            ->expects($this->once())
            ->method('getGoogleClientEnvVars')
            ->willReturn([
                'client_id' => '654888211248',
                'client_secret' => 'GOCSPX-tQO_KtlRtceIY',
            ]);

        $configMock
            ->expects($this->once())
            ->method('urlScheme')
            ->willReturn('http://');

        $configMock
            ->expects($this->once())
            ->method('getHost')
            ->willReturn('localhost:8000');

        $clientMock = $this->createMock(Client::class);

        $clientMock
            ->expects($this->once())
            ->method('setAuthConfig')
            ->with([
                'client_id' => '654888211248',
                'client_secret' => 'GOCSPX-tQO_KtlRtceIY',
            ]);

        $clientMock
            ->expects($this->once())
            ->method('setRedirectUri')
            ->with('http://localhost:8000/src/php/Oauth/googleCallback.php');

        $googleAuthorization = new GoogleAuthorization($configMock, $clientMock);
        $result = $googleAuthorization->setClient();

        $this->assertSame($clientMock, $result);
    }

    /**
     * 本番環境でのGoogle Client設定テスト
     */
    public function testProductionSetClient(): void
    {
        $configMock = $this->createMock(Config::class);

        $configMock
            ->expects($this->once())
            ->method('getGoogleClientEnvVars')
            ->willReturn([
                'client_id' => '654888211248',
                'client_secret' => 'GOCSPX-tQO_KtlRtceIY',
            ]);

        $configMock
            ->expects($this->once())
            ->method('urlScheme')
            ->willReturn('https://');

        $configMock
            ->expects($this->once())
            ->method('getHost')
            ->willReturn('www.vmatch.com');

        $clientMock = $this->createMock(Client::class);

        $clientMock
            ->expects($this->once())
            ->method('setAuthConfig')
            ->with([
                'client_id' => '654888211248',
                'client_secret' => 'GOCSPX-tQO_KtlRtceIY',
            ]);

        $clientMock
            ->expects($this->once())
            ->method('setRedirectUri')
            ->with('https://www.vmatch.com/src/php/Oauth/googleCallback.php');

        $googleAuthorization = new GoogleAuthorization($configMock, $clientMock);
        $result = $googleAuthorization->setClient();

        $this->assertSame($clientMock, $result);
    }

    /**
     * stateパラメーター設定・検証テスト
     */
    public function testverifyState(): void
    {
        $googleAuthorization = new GoogleAuthorization();

        $googleAuthorization->setState('test_state');

        $googleAuthorization->verifyState('test_state');

        $this->assertTrue(true);
    }

    /**
     * stateパラメーター不一致時の例外テスト
     */
    public function testVerifyStateException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $googleAuthorization = new GoogleAuthorization();

        $googleAuthorization->setState('test_state');

        $googleAuthorization->verifyState('invalid_state');
    }
}