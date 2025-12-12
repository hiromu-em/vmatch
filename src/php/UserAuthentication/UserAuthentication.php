<?php
declare(strict_types=1);

namespace Vmatch\UserAuthentication;

use Vmatch\Config;

/**
 * ユーザー認証に関わるクラス
 */
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
        $statement = $this->pdo->prepare("INSERT INTO users_vmatch(email) VALUES (?)");
        $statement->execute([$newEmail]);
    }

    /**
     * ユーザーIDを取得する
     * @param string $newEmail 新規ユーザーメールアドレス
     * @return string ユーザーID情報
     */
    public function getSearchUserId(string $newEmail): string
    {
        $statement = $this->pdo->prepare("SELECT id FROM users_vmatch WHERE email = ?");
        $statement->execute([$newEmail]);
        $result = $statement->fetch();

        return $result['id'];
    }

    /**
     * ユーザーのメールアドレスを確認する
     * @param string $newEmail 新規ユーザーメールアドレス
     * @return int 新規メールアドレスの結果
     */
    public function emailExists(?string $newEmail): int
    {
        $errorCode = 0;

        //NULLチェック
        if (empty($newEmail)) {
            return $errorCode = 3;
        }

        $query = "SELECT EXISTS(SELECT 1 FROM users_vmatch WHERE email = ?) as status";
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
    public function validateEmail(?string $newEmail): int
    {
        $errorCode = 0;

        //NULLチェック or 空文字チェック
        if (empty($newEmail)) {
            return $errorCode = 3;
        }

        // メールアドレスの形式チェック
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
        $statement = $this->pdo->prepare("UPDATE users_vmatch SET password_hash = ? WHERE email = ?");
        $statement->execute([$newPassword, $newEmail]);
    }

    /**
     * パスワードの形式を検証
     * @param string $newPassword 新規パスワード
     * @return array パスワード形式結果情報
     */
    public function validatePassword(?string $newPassword): array
    {
        $errorCodes = [];

        //NULLチェック or 空文字チェック
        if (empty($newPassword)) {
            return $errorCodes[] = [4];
        }

        // 文字列の長さチェック
        if (mb_strlen($newPassword) < 8) {
            $errorCodes[] = 5;
        }

        // 英字の有無チェック
        if (!preg_match('/[A-Za-z]/', $newPassword)) {
            $errorCodes[] = 6;
        }

        // 数字の有無チェック
        if (!preg_match('/\d/', $newPassword)) {
            $errorCodes[] = 7;
        }

        // 記号の有無チェック
        if (!preg_match('/[@#\$%\^&\*]/', $newPassword)) {
            $errorCodes[] = 8;
        }

        return $errorCodes;
    }

    /**
     * パスワードの照合
     * @param string $password パスワード
     * @param string $email メールアドレス
     * @return bool 照合結果
     */
    public function verifyPassword(string $password, string $email): bool
    {
        $statement = $this->pdo->prepare("SELECT password_hash FROM users_vmatch WHERE email = ?");
        $statement->execute([$email]);
        $result = $statement->fetch();

        if ($result && password_verify($password, $result['password_hash'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * プロバイダーIDの存在確認
     * @param string $providerId プロバイダーID
     * @return bool プロバイダーID存在結果
     */
    public function providerIdExists(string $providerId): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM users_vmatch_providers WHERE provider_user_id = ?) as status";
        $statement = $this->pdo->prepare($query);
        $statement->execute([$providerId]);

        $result = $statement->fetch();
        $emailExists = $result['status'] ? true : false;

        return $emailExists;

    }

    /**
     * プロバイダーIDとユーザーIDを紐付ける
     * @param array $userId ユーザーID
     * @param string $providerId プロバイダーID
     * @param string $provider プロパイダ―名
     */
    public function linkProviderUserId(string $userId, string $providerId, string $provider): void
    {
        $statement = $this->pdo->prepare("INSERT INTO users_vmatch_providers(user_id, provider, provider_user_id) VALUES (?, ?, ?)");
        $statement->execute([$userId, $provider, $providerId]);
    }

    /**
     * エラーメッセージを取得する
     * @param array $errorCodes エラーコード情報
     * @param bool $isLogin ログイン判定フラグ
     * @return array|string エラーメッセージ情報
     */
    public function errorMessages(array $errorCodes, bool $isLogin = false): array|string
    {
        $errorMessages = [];

        if ($isLogin) {
            $errorMessages = "メールアドレス\nまたはパスワードが正しくありません。";
            return $errorMessages;
        }

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
