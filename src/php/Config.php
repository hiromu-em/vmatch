<?php
declare(strict_types=1);

namespace Vmatch;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

class Config
{
    /**
     * データベース接続を確立する
     * @return \PDO データベース接続オブジェクト
     */
    public function databaseConnection(): \PDO
    {
        //本番環境と開発環境の分岐
        if ($this->loadDotenvIfLocal()) {

            $dsn = "pgsql:host={$_ENV['PGHOST']};port=21962;dbname={$_ENV['PGDATABASE']}";
            $user = $_ENV['PGUSER'];
            $password = $_ENV['PGPASSWORD'];
        } else {

            $host = getenv('PGHOST');
            $database = getenv('PGDATABASE');
            $dsn = "pgsql:host={$host};port=5432;dbname={$database}";
            $user = getenv('PGUSER');
            $password = getenv('PGPASSWORD');
        }

        $pdo = new \PDO($dsn, $user, $password);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        return $pdo;
    }

    /**
     * 環境変数をロード
     * @param bool $isLocal ローカル環境フラグ
     * @throws InvalidPathException 無効なパス
     * @return bool ローカル環境フラグの結果
     */
    public function loadDotenvIfLocal(bool $isLocal = false): bool
    {
        if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {

            // フラグ変更
            $isLocal = true;

            $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
            try {
                $dotenv->load(); 
            } catch (InvalidPathException $e) {
                http_response_code(500);
                include __DIR__ . '/error/databaseError.php';
                exit;
            }
        }

        return $isLocal;
    }

    /**
     * URLスキームを取得
     * @return string URLスキーム
     */
    public function urlScheme(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return (strpos($host, 'localhost') !== false) ? 'http://' : 'https://';
    }
}