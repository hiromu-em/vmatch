<?php
    namespace Vmatch;

    use Vmatch\DatabaseConfig;

    class UserRegistrationService{

        private $pdo;
        
        public function __construct(){
            $databaseConfig = new DatabaseConfig();
            $this -> pdo = $databaseConfig -> connection();
        }
        
        /**
         * ユーザーのメールアドレスを仮登録する
         * @param string $newUserEmail 新規ユーザーメールアドレス
         * @return void
         */
        public function registerTemporaryUser(string $newUserEmail): void{

            $statement = $this->pdo->prepare("INSERT INTO users (email) VALUES (?)");
            $statement -> execute([$newUserEmail]);
        }

        /**
         * ユーザーのメールアドレスを確認する
         * @param string $newUserEmail 新規ユーザーメールアドレス
         * @return void
         */
        public function isEmailRegistered(string $newUserEmail){

        }
    }
