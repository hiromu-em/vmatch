<?php
    namespace Vmatch;

    use PDOException;

    class DatabaseConfig{

        /**
         * データーベース接続
         * @return \PDO PDOインスタンス
         */
        public function connection(): \PDO{
            
            $dsn = "pgsql:host={$_ENV['PGHOST']};port=21962;dbname={$_ENV['PGDATABASE']}";
            $user = $_ENV['PGUSER'];
            $password = $_ENV['PGPASSWORD'];
            
            try{
                $pdo = new \PDO($dsn, $user, $password);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            
            }catch(PDOException $e){
                //エラーページ表示（後日実装）

            }

            return $pdo;
        }
    }