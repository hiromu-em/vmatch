<?php
namespace Vmatch;

use Vmatch\DatabaseConfig;

class UserRegistrationService
{

    private $pdo;

    public function __construct()
    {
        $databaseConfig = new DatabaseConfig();
        $this->pdo = $databaseConfig->connection();
    }

    /**
     * ユーザーのメールアドレスを仮登録する
     * @param string $newUserEmail 新規ユーザーメールアドレス
     */
    public function registerTemporaryUser(string $newUserEmail): void
    {
        $statement = $this->pdo->prepare("INSERT INTO users (email) VALUES (?)");
        $statement->execute([$newUserEmail]);
    }

    /**
     * ユーザーのメールアドレスを確認する
     * @param string $newUserEmail 新規ユーザーメールアドレス
     * @return bool `true`:登録済み  `false`:未登録
     */
    public function emailExists(string $newUserEmail): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM users WHERE email = ?)";
        $statement = $this->pdo->prepare($query);
        $statement->execute([$newUserEmail]);
        return (bool)$statement->fetchColumn();
    }
}
