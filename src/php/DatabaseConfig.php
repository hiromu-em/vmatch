<?php
    namespace Vmatch;

    use PDOException;

    class DatabaseConfig{

        /**
         * データーベース接続
         * @return \PDO PDOインスタンス
         */
        public function connection(): \PDO{

        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        if(strpos($httpHost, 'localhost') !== false) {
            $dsn = "pgsql:host={$_ENV['PGHOST']};port=21962;dbname={$_ENV['PGDATABASE']}";
            $user = $_ENV['PGUSER'];
            $password = $_ENV['PGPASSWORD'];
        
        }else{
            
            $host = getenv("PGHOST");
            $database = getenv("PGDATABASE");
            
            $dsn = "pgsql:host={$host};port=21962;dbname={$database}";
            $user = getenv('PGUSER');
            $password = getenv('PGPASSWORD');
        }

            
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