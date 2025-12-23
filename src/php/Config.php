<?php
declare(strict_types=1);

namespace Vmatch;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use PDOException;

class Config
{
    // ホスト名
    private string $host;

    public function __construct(string $host)
    {
        // ホスト名設定
        $this->host = $host;
    }

    /**
     * データベース接続を確立する
     * @return `pdo`データベース接続オブジェクト
     * @throws PDOException データベースの接続失敗
     */
    public function databaseConnection(): \PDO
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

        try {
            $pdo = new \PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            http_response_code(500);
            include __DIR__ . '/error/configError.php';
            exit;
        }

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        return $pdo;
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
     * @throws InvalidPathException 無効なパス
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