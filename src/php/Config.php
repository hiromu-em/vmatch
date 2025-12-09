<?php
declare(strict_types=1);

namespace Vmatch;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use PDOException;

class Config
{
    /**
     * データベース接続を確立する
     * @return `pdo`データベース接続オブジェクト
     * @throws PDOException データベースの接続失敗
     */
    public function databaseConnection(): \PDO
    {
        //本番環境と開発環境の分岐
        // 後日テーブル名を本番環境と開発環境で分ける
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
     * 環境変数をロード
     * @param bool $isLocal ローカル環境フラグ
     * @return bool ローカル環境フラグの結果
     * @throws InvalidPathException 無効なパス
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
                include __DIR__ . '/error/configError.php';
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
        return strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? 'http://' : 'https://';
    }
}