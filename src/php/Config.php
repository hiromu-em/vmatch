<?php
declare(strict_types=1);

namespace Vmatch;

use Dotenv\Dotenv;

class Config
{
    // ホスト名
    private string $host;

    /**
     * @param string $host ホスト名
     */
    public function __construct(string $host)
    {
        // ホスト名設定
        $this->host = $host;
    }

    /**
     * 環境に応じたデータベース接続の設定を取得
     * @return array データベース接続設定
     */
    public function getDatabaseSettings(): array
    {
        //本番環境と開発環境の分岐
        if ($this->loadDotenvIfLocal()) {

            $dsn = "pgsql:host={$_ENV['PG_LOCAL_HOST']};port=5432;dbname={$_ENV['PG_LOCAL_DATABASE']}";
            $user = $_ENV['PG_LOCAL_USER'];
            $password = $_ENV['PG_LOCAL_PASSWORD'];
        } else {

            $host = getenv('PGHOST');
            $database = getenv('PGDATABASE');
            $dsn = "pgsql:host={$host};port=5432;dbname={$database}";
            $user = getenv('PGUSER');
            $password = getenv('PGPASSWORD');
        }

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
     * ホスト名を取得
     * @return string ホスト名
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * ローカル環境の場合、.envファイルを読み込む
     * @return bool ローカル環境フラグの結果
     */
    public function loadDotenvIfLocal(): bool
    {
        if (strpos($this->getHost(), 'localhost') !== false) {

            $this->loadDotenv();

            return true;
        }

        return false;
    }

    /**
     * .envファイルを読み込む
     */
    public function loadDotenv()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();
    }

    /**
     * URLスキームを取得
     * @return string URLスキーム
     */
    public function urlScheme(): string
    {
        return strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? 'http://' : 'https://';
    }
}