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
     * ユーザーのメールアドレスをDBに登録する
     * @param string $newUserEmail 新規ユーザーメールアドレス
     */
    public function registerEmail(string $newUserEmail): void
    {
        $statement = $this->pdo->prepare("INSERT INTO users (email) VALUES (?)");
        $statement->execute([$newUserEmail]);
    }

    /**
     * ユーザーのメールアドレスを確認する
     * @param string $newUserEmail 新規ユーザーメールアドレス
     * @return int 新規メールアドレスの結果情報
     */
    public function emailExists(?string $newUserEmail): int
    {
        $errorCode = 0;

        //NULLチェック
        if (empty($newUserEmail)) {
            return $errorCode = 3;
        }

        $query = "SELECT EXISTS(SELECT 1 FROM users WHERE email = ?) as status";
        $statement = $this->pdo->prepare($query);
        $statement->execute([$newUserEmail]);
        $result = $statement->fetch();

        $errorCode = $result['status'] ? 1 : 0;
        return $errorCode;
    }


    /**
     * メールアドレス検証
     * @param string $newUserEmail 新規ユーザーメールアドレス
     * @return int メールアドレス形式の結果情報
     */
    public function validateEmail(?string $newUserEmail): int
    {
        $errorCode = 0;

        //NULLチェック
        if (empty($newUserEmail)) {
            return $errorCode = 3;
        }

        $email = trim($newUserEmail);

        // 空文字チェック、メールアドレスの形式チェック
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $errorCode = 2;
        }

        //ドメイン存在チェック
        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, "MX")) {
            return $errorCode = 2;
        }

        return $errorCode;
    }
    /**
     * 新規ユーザーのパスワードをDBに登録する
     * @param string $newPassword 新規パスワード
     * @param string $newEmail 新規メールアドレス
     */
    public function registerPassword(string $newPassword, string $newEmail): void
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $statement = $this->pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $statement->execute([$passwordHash, $newEmail]);
    }

    /**
     * 新規登録時のエラーを表示
     * @return array エラーメッセージ情報
     */
    public function registrationError(array $errorCodes): array
    {
        $errorMessages = [];

        foreach ($errorCodes as $errorCode) {
            switch ($errorCode) {
                case 0:
                    break;
                case 1:
                    $errorMessages[] = "登録済みユーザーです。\nログインしてください";
                    break;
                case 2:
                    $errorMessages[] = "メールアドレスの形式が正しくありません。";
                    break;
                case 3:
                    $errorMessages[] = "メールアドレスを入力してください。";
                    break;
            }
        }

        return $errorMessages;
    }
}
