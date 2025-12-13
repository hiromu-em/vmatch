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

    private array $errorCodes = [];

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
     * @param bool $loginFlag ログインフラグ
     * @return bool メールアドレス存在結果
     */
    public function emailExists(?string $newEmail, bool $loginFlag = true): bool
    {
        //NULLチェック
        if (empty($newEmail)) {
            $this->errorCodes[] = 3;
            return true;
        }

        $query = "SELECT EXISTS(SELECT 1 FROM users_vmatch WHERE email = ?) as status";
        $statement = $this->pdo->prepare($query);
        $statement->execute([$newEmail]);

        $result = $statement->fetch();

        /**
         * 登録済みユーザーの場合、登録処理時はエラーコードを追加
         * ログイン処理時は追加しない
         */
        if ($result['status'] && !$loginFlag) {
            $this->errorCodes[] = 1;
        }

        return $result['status'] ? true : false;
    }

    /**
     * メールアドレス検証
     * @param string $newEmail 新規ユーザーメールアドレス
     * @return bool メールアドレス形式結果
     */
    public function validateEmail(?string $newEmail): bool
    {
        //NULLチェック or 空文字チェック
        if (empty($newEmail)) {
            $this->errorCodes[] = 3;
            return false;
        }

        // メールアドレスの形式チェック
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $this->errorCodes[] = 2;
            return false;
        }

        //ドメイン存在チェック
        if (!checkdnsrr(substr(strrchr($newEmail, "@"), 1), "MX")) {
            $this->errorCodes[] = 2;
            return false;
        }

        return true;
    }

    /**
     * パスワードの形式を検証
     * @param string $newPassword 新規パスワード
     * @return bool パスワード形式結果
     */
    public function validatePassword(?string $newPassword): bool
    {
        //NULLチェック or 空文字チェック
        if (empty($newPassword)) {
            $this->errorCodes[] = 4;
            return false;
        }

        // 文字列の長さチェック
        if (mb_strlen($newPassword) < 8) {
            $this->errorCodes[] = 5;
        }

        // 英字の有無チェック
        if (!preg_match('/[A-Za-z]/', $newPassword)) {
            $this->errorCodes[] = 6;
        }

        // 数字の有無チェック
        if (!preg_match('/\d/', $newPassword)) {
            $this->errorCodes[] = 7;
        }

        // 記号の有無チェック
        if (!preg_match('/[@#\$%\^&\*]/', $newPassword)) {
            $this->errorCodes[] = 8;
        }

        if (!empty($this->errorCodes)) {
            return false;
        }

        return true;
    }

    /**
     * 新規ユーザー登録処理
     * @param string $email ユーザーメールアドレス
     * @param string $passwordHash ハッシュ化パスワード
     * @return void
     */
    public function userRegistration($email, $passwordHash): void
    {
        $stetement = $this->pdo->prepare("INSERT INTO users_vmatch(email, password_hash) VALUES (?, ?)");
        $stetement->execute([$email, $passwordHash]);
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
     * ログインエラーメッセージを取得する
     * @return string エラーメッセージ情報
     */
    public function loginErrorMessage(): string
    {
        $errorMessage = "メールアドレス\nまたはパスワードが正しくありません。";
        return $errorMessage;
    }

    /**
     * エラーメッセージを取得する
     * @param array $errorCodes エラーコード情報
     * @return array エラーメッセージ情報
     */
    public function errorMessages(): array
    {
        $errorCodes = array_unique($this->errorCodes);
        foreach ($errorCodes as $errorCode) {
            switch ($errorCode) {
                case 0:
                    break;
                case 1:
                    $errorMessages[] = "登録済みユーザーです。ログインしてください。";
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
