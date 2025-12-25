<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Vmatch\Config;
use Dotenv\Dotenv;

class ConfigTest extends TestCase
{
    /**
     * テスト用のConfigクラスのモック
     */
    private function createTestableConfig(string $host)
    {
        return new class ($host) extends Config {
            private array $envVars = [];
            private array $serverVars = [];
            private ?Dotenv $mockDotenv = null;

            public function setEnvVar(string $key, $value): void
            {
                $this->envVars[$key] = $value;
            }

            public function setServerVar(string $key, $value): void
            {
                $this->serverVars[$key] = $value;
            }

            public function setMockDotenv(Dotenv $dotenv): void
            {
                $this->mockDotenv = $dotenv;
            }

            protected function getEnv(string $key)
            {
                return $this->envVars[$key] ?? false;
            }

            protected function getServerVar(string $key): ?string
            {
                return $this->serverVars[$key] ?? null;
            }

            protected function createDotenv(): Dotenv
            {
                return $this->mockDotenv ?? parent::createDotenv();
            }
        };
    }

    /**
     * ローカル環境の判定テスト
     */
    public function testIsLocalEnvironment(): void
    {
        // ローカル環境
        $config = new Config('localhost');
        $this->assertTrue($config->isLocalEnvironment());

        // 本番環境
        $config = new Config('example.com');
        $this->assertFalse($config->isLocalEnvironment());
    }

    /**
     * ホスト名取得テスト
     */
    public function testGetHost(): void
    {
        $config = new Config('localhost');
        $this->assertEquals('localhost', $config->getHost());

        $config = new Config('example.com');
        $this->assertEquals('example.com', $config->getHost());
    }

    /**
     * ローカル環境のデータベース設定取得テスト
     */
    public function testGetLocalDatabaseSettings(): void
    {
        $config = $this->createTestableConfig('localhost');

        // テスト用の環境変数を設定
        $config->setEnvVar('PG_LOCAL_HOST', 'localhost');
        $config->setEnvVar('PG_LOCAL_DATABASE', 'test_db');
        $config->setEnvVar('PG_LOCAL_USER', 'test_user');
        $config->setEnvVar('PG_LOCAL_PASSWORD', 'test_pass');

        // Dotenvモックを設定
        $mockDotenv = $this->createMock(Dotenv::class);
        $mockDotenv->expects($this->once())->method('load');
        $config->setMockDotenv($mockDotenv);

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
        $config = $this->createTestableConfig('example.com');

        // テスト用の環境変数を設定
        $config->setEnvVar('PGHOST', 'prod-host.com');
        $config->setEnvVar('PGDATABASE', 'prod_db');
        $config->setEnvVar('PGUSER', 'prod_user');
        $config->setEnvVar('PGPASSWORD', 'prod_pass');

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
        $config = $this->createTestableConfig('example.com');

        $config->setEnvVar('PGHOST', 'host');
        $config->setEnvVar('PGDATABASE', 'db');
        $config->setEnvVar('PGUSER', 'user');
        $config->setEnvVar('PGPASSWORD', 'pass');

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
        $config = $this->createTestableConfig('localhost');
        $config->setServerVar('HTTP_HOST', 'localhost:8080');

        $scheme = $config->urlScheme();
        $this->assertEquals('http://', $scheme);
    }

    /**
     * URLスキーム取得テスト（本番環境）
     */
    public function testUrlSchemeForProduction(): void
    {
        $config = $this->createTestableConfig('example.com');
        $config->setServerVar('HTTP_HOST', 'example.com');

        $scheme = $config->urlScheme();
        $this->assertEquals('https://', $scheme);
    }

    /**
     * URLスキーム取得テスト（HTTP_HOSTが未設定）
     */
    public function testUrlSchemeWithoutHttpHost(): void
    {
        $config = $this->createTestableConfig('example.com');

        $scheme = $config->urlScheme();
        $this->assertEquals('https://', $scheme);
    }

    /**
     * loadDotenvIfLocalのテスト（ローカル環境）
     */
    public function testLoadDotenvIfLocalForLocalhost(): void
    {
        $config = $this->createTestableConfig('localhost');

        $mockDotenv = $this->createMock(Dotenv::class);
        $mockDotenv->expects($this->once())->method('load');
        $config->setMockDotenv($mockDotenv);

        $result = $config->loadDotenvIfLocal();
        $this->assertTrue($result);
    }

    /**
     * loadDotenvIfLocalのテスト（本番環境）
     */
    public function testLoadDotenvIfLocalForProduction(): void
    {
        $config = $this->createTestableConfig('example.com');

        $mockDotenv = $this->createMock(Dotenv::class);
        $mockDotenv->expects($this->never())->method('load');
        $config->setMockDotenv($mockDotenv);

        $result = $config->loadDotenvIfLocal();
        $this->assertFalse($result);
    }

    /**
     * 環境変数が空の場合のテスト
     */
    public function testGetDatabaseSettingsWithEmptyEnvVars(): void
    {
        $config = $this->createTestableConfig('example.com');

        // 環境変数を設定しない（falseが返される）
        $settings = $config->getDatabaseSettings();

        $this->assertIsArray($settings);
        $this->assertFalse($settings['user']);
        $this->assertFalse($settings['password']);
    }

    /**
     * Googleクライアント環境変数取得テスト（正常系）
     */
    public function testGetGoogleClientEnvVars(): void
    {
        $config = $this->createTestableConfig('localhost');

        // テスト用の環境変数を設定
        $config->setEnvVar('CLIENTID', 'test-client-id-12345');
        $config->setEnvVar('CLIENTSECRET', 'test-client-secret-67890');

        $envVars = $config->getGoogleClientEnvVars();

        // 配列構造の検証
        $this->assertIsArray($envVars);
        $this->assertArrayHasKey('client_id', $envVars);
        $this->assertArrayHasKey('client_secret', $envVars);

        // 値の検証
        $this->assertEquals('test-client-id-12345', $envVars['client_id']);
        $this->assertEquals('test-client-secret-67890', $envVars['client_secret']);
    }

    /**
     * Googleクライアント環境変数取得テスト（環境変数未設定）
     */
    public function testGetGoogleClientEnvVarsWithMissingEnvVars(): void
    {
        $config = $this->createTestableConfig('localhost');

        // 環境変数を設定しない
        $envVars = $config->getGoogleClientEnvVars();

        // 配列構造の検証
        $this->assertIsArray($envVars);
        $this->assertArrayHasKey('client_id', $envVars);
        $this->assertArrayHasKey('client_secret', $envVars);

        // 環境変数が未設定の場合はfalseが返される
        $this->assertFalse($envVars['client_id']);
        $this->assertFalse($envVars['client_secret']);
    }

    /**
     * Googleクライアント環境変数取得テスト（一部のみ設定）
     */
    public function testGetGoogleClientEnvVarsWithPartialEnvVars(): void
    {
        $config = $this->createTestableConfig('localhost');

        // client_idのみ設定
        $config->setEnvVar('CLIENTID', 'test-client-id');

        $envVars = $config->getGoogleClientEnvVars();

        // 配列構造の検証
        $this->assertIsArray($envVars);
        $this->assertEquals('test-client-id', $envVars['client_id']);
        $this->assertFalse($envVars['client_secret']);
    }
}



