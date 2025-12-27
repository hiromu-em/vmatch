<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Vmatch\Config;
use Dotenv\Dotenv;

class ConfigTest extends TestCase
{
    /**
     * ローカル環境の判定テスト
     */
    public function testIsLocalEnvironment(): void
    {
        // ローカル環境
        $config = new Config();
        $config->setHost('localhost');
        $this->assertTrue($config->isLocalEnvironment());

        // 本番環境
        $config = new Config();
        $config->setHost('example.com');
        $this->assertFalse($config->isLocalEnvironment());
    }

    /**
     * ホスト名取得テスト
     */
    public function testGetHost(): void
    {
        $config = new Config();
        $config->setHost('localhost');
        $this->assertEquals('localhost', $config->getHost());

        $config = new Config();
        $config->setHost('example.com');
        $this->assertEquals('example.com', $config->getHost());
    }

    /**
     * ローカル環境のデータベース設定取得テスト
     */
    public function testGetLocalDatabaseSettings(): void
    {
        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getEnv', 'getHost'])
            ->getMock();

        $config
            ->expects($this->once())
            ->method('getHost')
            ->willReturn('localhost');

        $config
            ->expects($this->exactly(4))
            ->method('getEnv')
            ->willReturnMap([
                ['PG_LOCAL_HOST', 'localhost'],
                ['PG_LOCAL_DATABASE', 'test_db'],
                ['PG_LOCAL_USER', 'test_user'],
                ['PG_LOCAL_PASSWORD', 'test_pass']
            ]);

        $settings = $config->getDatabaseSettings();

        $this->assertIsArray($settings);
        $this->assertArrayHasKey('dsn', $settings);
        $this->assertArrayHasKey('user', $settings);
        $this->assertArrayHasKey('password', $settings);
        $this->assertArrayHasKey('options', $settings);

        $this->assertEquals('pgsql:host=localhost;port=5432;dbname=test_db', $settings['dsn']);
        $this->assertEquals('test_user', $settings['user']);
        $this->assertEquals('test_pass', $settings['password']);
        $this->assertIsArray($settings['options']);
    }

    /**
     * 本番環境のデータベース設定取得テスト
     */
    public function testGetProductionDatabaseSettings(): void
    {
        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getEnv', 'getHost'])
            ->getMock();

        $config
            ->expects($this->once())
            ->method('getHost')
            ->willReturn('example.com');

        $config
            ->expects($this->exactly(4))
            ->method('getEnv')
            ->willReturnMap([
                ['PGHOST', 'prod-host.com'],
                ['PGDATABASE', 'prod_db'],
                ['PGUSER', 'prod_user'],
                ['PGPASSWORD', 'prod_pass']
            ]);

        $settings = $config->getDatabaseSettings();

        $this->assertIsArray($settings);
        $this->assertArrayHasKey('dsn', $settings);
        $this->assertArrayHasKey('user', $settings);
        $this->assertArrayHasKey('password', $settings);
        $this->assertArrayHasKey('options', $settings);

        $this->assertEquals('pgsql:host=prod-host.com;port=5432;dbname=prod_db', $settings['dsn']);
        $this->assertEquals('prod_user', $settings['user']);
        $this->assertEquals('prod_pass', $settings['password']);
    }

    /**
     * データベース設定のオプション検証テスト
     */
    public function testDatabaseSettingsOptions(): void
    {
        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getEnv', 'getHost'])
            ->getMock();

        $config
            ->expects($this->once())
            ->method('getHost')
            ->willReturn('example.com');

        $config
            ->expects($this->exactly(4))
            ->method('getEnv')
            ->willReturnMap([
                ['PGHOST', 'host'],
                ['PGDATABASE', 'db'],
                ['PGUSER', 'user'],
                ['PGPASSWORD', 'pass']
            ]);

        $settings = $config->getDatabaseSettings();

        $this->assertArrayHasKey(\PDO::ATTR_ERRMODE, $settings['options']);
        $this->assertArrayHasKey(\PDO::ATTR_DEFAULT_FETCH_MODE, $settings['options']);
        $this->assertEquals(\PDO::ERRMODE_EXCEPTION, $settings['options'][\PDO::ATTR_ERRMODE]);
        $this->assertEquals(\PDO::FETCH_ASSOC, $settings['options'][\PDO::ATTR_DEFAULT_FETCH_MODE]);
    }

    /**
     * URLスキーム取得テスト（ローカル環境）
     */
    public function testUrlSchemeForLocalhost(): void
    {
        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getServerVar'])
            ->getMock();

        $config
            ->expects($this->once())
            ->method('getServerVar')
            ->with('HTTP_HOST')
            ->willReturn('localhost:8080');

        $scheme = $config->urlScheme();
        $this->assertEquals('http://', $scheme);
    }

    /**
     * URLスキーム取得テスト（本番環境）
     */
    public function testUrlSchemeForProduction(): void
    {
        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getServerVar'])
            ->getMock();

        $config
            ->expects($this->once())
            ->method('getServerVar')
            ->with('HTTP_HOST')
            ->willReturn('example.com');

        $scheme = $config->urlScheme();
        $this->assertEquals('https://', $scheme);
    }

    /**
     * URLスキーム取得テスト（HTTP_HOSTが未設定）
     */
    public function testUrlSchemeWithoutHttpHost(): void
    {
        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getServerVar'])
            ->getMock();

        $config
            ->expects($this->once())
            ->method('getServerVar')
            ->with('HTTP_HOST')
            ->willReturn(null);

        $scheme = $config->urlScheme();
        $this->assertEquals('https://', $scheme);
    }

    /**
     * loadDotenvIfLocalのテスト（ローカル環境）
     */
    public function testLoadDotenvIfLocalForLocalhost(): void
    {
        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['createDotenv', 'getHost'])
            ->getMock();

        $config
            ->expects($this->once())
            ->method('getHost')
            ->willReturn('localhost');

        $mockDotenv = $this->createMock(Dotenv::class);
        $mockDotenv
            ->expects($this->once())
            ->method('load');

        $config
            ->expects($this->once())
            ->method('createDotenv')
            ->willReturn($mockDotenv);

        $result = $config->loadDotenvIfLocal();
        $this->assertTrue($result);
    }

    /**
     * loadDotenvIfLocalのテスト（本番環境）
     */
    public function testLoadDotenvIfLocalForProduction(): void
    {
        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['createDotenv', 'getHost'])
            ->getMock();

        $config
            ->expects($this->once())
            ->method('getHost')
            ->willReturn('example.com');

        $mockDotenv = $this->createMock(Dotenv::class);
        $mockDotenv
            ->expects($this->never())
            ->method('load');

        $config
            ->expects($this->never())
            ->method('createDotenv')
            ->willReturn($mockDotenv);

        $result = $config->loadDotenvIfLocal();
        $this->assertFalse($result);
    }

    /**
     * Googleクライアント環境変数取得テスト
     */
    public function testGetGoogleClientEnvVars(): void
    {
        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getEnv'])
            ->getMock();

        // テスト用の環境変数を設定
        $config
            ->expects($this->exactly(2))
            ->method('getEnv')
            ->willReturnMap([
                ['CLIENTID', 'test-client-id-12345'],
                ['CLIENTSECRET', 'test-client-secret-67890']
            ]);

        $envVars = $config->getGoogleClientEnvVars();

        // 配列構造の検証
        $this->assertIsArray($envVars);
        $this->assertArrayHasKey('client_id', $envVars);
        $this->assertArrayHasKey('client_secret', $envVars);

        // 値の検証
        $this->assertEquals('test-client-id-12345', $envVars['client_id']);
        $this->assertEquals('test-client-secret-67890', $envVars['client_secret']);
    }
}



