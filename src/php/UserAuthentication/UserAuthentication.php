<?php
declare(strict_types=1);

namespace Vmatch\UserAuthentication;

use Vmatch\Config;

class UserAuthentication
{
    private $pdo;

    public function __construct()
    {
        $databaseConfig = new Config();
        $this->pdo = $databaseConfig->databaseConnection();
    }

    /**
     * ユーザーのメールアドレスをDBに登録する
     * @param string $newEmail 新規ユーザーメールアドレス
     */
    public function registerEmail(string $newEmail): void
    {
        $statement = $this->pdo->prepare("INSERT INTO users(email) VALUES (?)");
        $statement->execute([$newEmail]);
    }

    /**
     * ユーザーのメールアドレスを確認する
     * @param string $newEmail 新規ユーザーメールアドレス
     * @param int $errorCode エラーコード
     * @return int 新規メールアドレスの結果
     */
    public function emailExists(?string $newEmail, $errorCode = 0): int
    {
        //NULLチェック
        if (empty($newEmail)) {
            return $errorCode = 3;
        }

        $query = "SELECT EXISTS(SELECT 1 FROM users WHERE email = ?) as status";
        $statement = $this->pdo->prepare($query);
        $statement->execute([$newEmail]);

        $result = $statement->fetch();
        $errorCode = $result['status'] ? 1 : 0;

        return $errorCode;
    }

    /**
     * メールアドレス検証
     * @param string $newEmail 新規ユーザーメールアドレス
     * @return int メールアドレス形式の結果情報
     */
    public function validateEmail(?string $newEmail, $errorCode = 0): int
    {
        //NULLチェック
        if (empty($newEmail)) {
            return $errorCode = 3;
        }

        // 空文字チェック、メールアドレスの形式チェック
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            return $errorCode = 2;
        }

        //ドメイン存在チェック
        if (!checkdnsrr(substr(strrchr($newEmail, "@"), 1), "MX")) {
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
        $statement = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $statement->execute([$newPassword, $newEmail]);
    }

    /**
     * パスワードの形式を検証
     * @param string $newPassword 新規パスワード
     * @return array パスワード形式結果情報
     */
    public function validatePassword(?string $newPassword, $errorCodes = []): array
    {
        //NULLチェック
        if (empty($newPassword)) {
            return $errorCodes[] = [4];
        }

        if (mb_strlen($newPassword) < 8) {
            $errorCodes[] = 5;
        }

        if (!preg_match('/[A-Za-z]/', $newPassword)) {
            $errorCodes[] = 6;
        }

        if (!preg_match('/\d/', $newPassword)) {
            $errorCodes[] = 7;
        }

        if (!preg_match('/[@#\$%\^&\*]/', $newPassword)) {
            $errorCodes[] = 8;
        }

        return $errorCodes;
    }

    /**
     * 新規登録時のエラーメッセージを取得する
     * @param array $errorCodes エラーコード情報
     * @param array $errorMessages エラーメッセージ情報
     * @return array エラーメッセージ結果
     */
    public function registrationError(array $errorCodes, $errorMessages = []): array
    {
        foreach ($errorCodes as $errorCode) {
            switch ($errorCode) {
                case 0:
                    break;
                case 1:
                    $errorMessages[] = "登録済みユーザーです。\nログインしてください。";
                    break;
                case 2:
                    $errorMessages[] = "メールアドレスの形式が正しくありません。";
                    break;
                case 3:
                    $errorMessages[] = "メールアドレスを入力してください。";
                    break;
                case 4:
                    $errorMessages[] = "パスワードを入力してください。";
                    break;
                case 5:
                    $errorMessages[] = "8文字以上入力してください。";
                    break;
                case 6:
                    $errorMessages[] = "英字を1文字含めてください。";
                    break;
                case 7:
                    $errorMessages[] = "数字を1文字含めてください。";
                    break;
                case 8:
                    $errorMessages[] = "記号(@ # $ % ^ & *) を1文字含めてください。";
                    break;
            }
        }

        return $errorMessages;
    }
}
