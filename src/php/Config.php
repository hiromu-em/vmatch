<?php
declare(strict_types=1);

namespace Vmatch;

use Dotenv\Dotenv;

class Config implements ConfigInterface
{
    /**
     * ホスト名
     * @var string
     */
    private string $host = '';

    /**
     * 環境に応じたデータベース接続の設定を取得
     * @return array データベース接続設定
     */
    public function getDatabaseSettings(): array
    {
        //本番環境と開発環境の分岐
        if ($this->loadDotenvIfLocal()) {
            return $this->getLocalDatabaseSettings();
        } else {
            return $this->getProductionDatabaseSettings();
        }
    }

    /**
     * ローカル環境用のデータベース設定を取得
     * @return array データベース接続設定
     */
    public function getLocalDatabaseSettings(): array
    {
        $host = $this->getEnv('PG_LOCAL_HOST');
        $database = $this->getEnv('PG_LOCAL_DATABASE');
        $dsn = "pgsql:host={$host};port=5432;dbname={$database}";
        $user = $this->getEnv('PG_LOCAL_USER');
        $password = $this->getEnv('PG_LOCAL_PASSWORD');

        return $this->buildDatabaseSettings($dsn, $user, $password);
    }

    /**
     * 本番環境用のデータベース設定を取得
     * @return array データベース接続設定
     */
    public function getProductionDatabaseSettings(): array
    {
        $host = $this->getEnv('PGHOST');
        $database = $this->getEnv('PGDATABASE');
        $dsn = "pgsql:host={$host};port=5432;dbname={$database}";
        $user = $this->getEnv('PGUSER');
        $password = $this->getEnv('PGPASSWORD');

        return $this->buildDatabaseSettings($dsn, $user, $password);
    }

    /**
     * データベース設定配列を構築
     * @param string $dsn DSN文字列
     * @param string|false $user ユーザー名
     * @param string|false $password パスワード
     * @return array データベース接続設定
     */
    public function buildDatabaseSettings(string $dsn, $user, $password): array
    {
        return [
            'dsn' => $dsn,
            'user' => $user,
            'password' => $password,
            'options' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]
        ];
    }

    /**
     * 環境変数を取得（テスト時にオーバーライド可能）
     * @param string $key 環境変数のキー
     * @return string|false 環境変数の値
     */
    public function getEnv(string $key)
    {
        // $_ENVが設定されている場合はそちらを優先
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        return getenv($key);
    }

    /**
     * サーバー変数を取得（テスト時にオーバーライド可能）
     * @param string $key サーバー変数のキー
     * @return string|null サーバー変数の値
     */
    public function getServerVar(string $key): ?string
    {
        return $_SERVER[$key] ?? null;
    }

    /**
     * ホスト名を取得
     * @return string ホスト名
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * ホスト名を取得
     * @param string $host ホスト名
     * @return string ホスト名
     */
    public function setHost(string $host): string
    {
        return $this->host = $host;
    }

    /**
     * ローカル環境かどうかを判定
     * @return bool ローカル環境の場合はtrue
     */
    public function isLocalEnvironment(): bool
    {
        return strpos($this->getHost(), 'localhost') !== false;
    }

    /**
     * ローカル環境の場合、.envファイルを読み込む
     * @return bool ローカル環境フラグの結果
     */
    public function loadDotenvIfLocal(): bool
    {
        if ($this->isLocalEnvironment()) {
            $this->loadDotenv();
            return true;
        }

        return false;
    }

    /**
     * .envファイルを読み込む
     */
    public function loadDotenv(): void
    {
        $dotenv = $this->createDotenv();
        $dotenv->load();
    }

    /**
     * Dotenvインスタンスを作成（テスト時にオーバーライド可能）
     * @return Dotenv Dotenvインスタンス
     */
    public function createDotenv(): Dotenv
    {
        return Dotenv::createImmutable(__DIR__ . '/../..');
    }

    /**
     * URLスキームを取得
     * @return string URLスキーム
     */
    public function urlScheme(): string
    {
        $httpHost = $this->getServerVar('HTTP_HOST');
        return $httpHost && strpos($httpHost, 'localhost') !== false ? 'http://' : 'https://';
    }

    /**
     * Googleクライアントの環境変数を取得
     * @return array Googleクライアントの環境変数
     */
    public function getGoogleClientEnvVars(): array
    {
        return [
            'client_id' => $this->getEnv('CLIENTID'),
            'client_secret' => $this->getEnv('CLIENTSECRET'),
        ];
    }
}