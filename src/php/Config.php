<?php
declare(strict_types=1);

namespace Vmatch;

use PDOException;

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

        try {
            $pdo = new \PDO($dsn, $user, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            //エラーページ表示（後日実装）
            echo $e->getMessage();
        }

        return $pdo;
    }

    /**
     * ローカル環境であれば$_ENVをロードする
     * @param bool $isLocal ローカル環境フラグ
     * @return bool ローカル環境フラグの結果
     */
    public function loadDotenvIfLocal(bool $isLocal = false): bool
    {
        if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
            $dotenv->load();

            $isLocal = true;
        }

        return $isLocal;
    }
}