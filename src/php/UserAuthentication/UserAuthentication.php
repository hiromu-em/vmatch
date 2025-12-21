<?php
declare(strict_types=1);

namespace Vmatch\UserAuthentication;

/**
 * ユーザー認証に関わるクラス
 */
class UserAuthentication
{
    // ユーザーメールアドレス
    private string $userEmail = '';

    // エラーコード配列
    private array $errorCodes = [];

    // 認証済みユーザー情報
    private array $authenticatedUser = [];

    /**
     * @param \PDO $databaseConnection データベース接続
     */
    public function __construct(private ?\PDO $databaseConnection = null)
    {
    }

    /**
     * メールアドレスをDBに登録する
     * @param string $newEmail 新規ユーザーメールアドレス
     */
    public function registerEmail(string $newEmail): void
    {
        $statement = $this->databaseConnection->prepare("INSERT INTO users_vmatch(email) VALUES (?)");
        $statement->execute([$newEmail]);
    }

    /**
     * ユーザーIDを取得する
     * @param string $newEmail 新規ユーザーメールアドレス
     * @return string ユーザーID情報
     */
    public function getSearchUserId(string $newEmail): string
    {
        $statement = $this->databaseConnection->prepare("SELECT id FROM users_vmatch WHERE email = ?");
        $statement->execute([$newEmail]);
        $result = $statement->fetch();

        return $result['id'];
    }

    /**
     * メールアドレスを確認する
     * @param string $email メールアドレス
     * @return bool メールアドレス存在結果
     */
    public function emailExists(string $email): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM users_vmatch WHERE email = ?) as status";
        $statement = $this->databaseConnection->prepare($query);
        $statement->execute([$email]);

        $result = $statement->fetch();

        return $result['status'] ? true : false;
    }

    /**
     * サインインコード設定
     * @param bool $emailExists メールアドレス存在フラグ
     * @param bool $signIn サインインフラグ
     * @return array エラーコード配列
     */
    public function setSignInCodes(bool $emailExists, bool $signIn): void
    {
        if ($emailExists && !$signIn) {
            $this->setErrorCodes(1);
            return;
        }
    }

    /**
     * メールアドレス検証
     * @param string|null $email
     * @return bool メールアドレス形式結果
     */
    public function validateEmail(?string $email): bool
    {
        //NULLチェック or 空文字チェック
        if (empty($email)) {
            $this->setErrorCodes(3);
            return false;
        }

        $email = trim($email);

        // メールアドレスの形式チェック
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setErrorCodes(2);
            return false;
        }

        //ドメイン存在チェック
        if (!checkdnsrr(substr(strrchr($email, "@"), 1), "MX")) {
            $this->setErrorCodes(2);
            return false;
        }

        // ユーザーメールアドレスを設定
        $this->setEmail($email);

        return true;
    }

    /**
     * ユーザーメールアドレスを設定する
     * @param string $email ユーザーメールアドレス
     */
    public function setEmail(string $email): void
    {
        $this->userEmail = $email;
    }

    /**
     * ユーザーメールアドレスを取得する
     * @return string ユーザーメールアドレス
     */
    public function getEmail(): string
    {
        return $this->userEmail;
    }

    /**
     * パスワードの形式を検証
     * @param string|null $password
     * @return bool パスワード形式結果
     */
    public function validatePassword(?string $password): bool
    {
        //NULLチェック or 空文字チェック
        if (empty($password)) {
            $this->setErrorCodes(4);
            return false;
        }

        // 文字列の長さチェック
        if (mb_strlen($password) < 8) {
            $this->setErrorCodes(5);
        }

        // 英字の有無チェック
        if (!preg_match('/[A-Za-z]/', $password)) {
            $this->setErrorCodes(6);
        }

        // 数字の有無チェック
        if (!preg_match('/\d/', $password)) {
            $this->setErrorCodes(7);
        }

        // 記号の有無チェック
        if (!preg_match('/[@#\$%\^&\*]/', $password)) {
            $this->setErrorCodes(8);
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
        $stetement = $this->databaseConnection->prepare("INSERT INTO users_vmatch(email, password_hash) VALUES (?, ?)");
        $stetement->execute([$email, $passwordHash]);
    }

    /**
     * パスワードの照合
     * @param string $email ユーザーメールアドレス
     * @param string $password パスワード
     * @return bool 照合結果
     */
    public function verifyPassword(string $email, string $password): bool
    {
        $statement = $this->databaseConnection->prepare("SELECT password_hash FROM users_vmatch WHERE email = ?");
        $statement->execute([$email]);
        $result = $statement->fetch();

        if ($result && password_verify($password, $result['password_hash'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 認証済みユーザーを設定する
     * @param string $email メールアドレス
     * @param string $password パスワード
     */
    public function setAuthenticatedUser($email, $password)
    {
        $this->authenticatedUser['email'] = $email;
        $this->authenticatedUser['password'] = $password;
    }

    /**
     * 認証済みユーザー情報を取得する
     * @return array 認証済みユーザー情報
     */
    public function getAuthenticatedUser(): array
    {
        return $this->authenticatedUser;
    }

    /**
     * プロバイダーIDの存在確認
     * @param string $providerId プロバイダーID
     * @return bool プロバイダーID存在結果
     */
    public function providerIdExists(string $providerId): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM users_vmatch_providers WHERE provider_user_id = ?) as status";
        $statement = $this->databaseConnection->prepare($query);
        $statement->execute([$providerId]);
        $result = $statement->fetch();

        return $result['status'] ? true : false;

    }

    /**
     * プロバイダーIDとユーザーIDを紐付ける
     * @param array $userId ユーザーID
     * @param string $providerId プロバイダーID
     * @param string $provider プロパイダ―名
     */
    public function linkProviderUserId(string $userId, string $providerId, string $provider): void
    {
        $statement = $this->databaseConnection->prepare("INSERT INTO users_vmatch_providers(user_id, provider, provider_user_id) VALUES (?, ?, ?)");
        $statement->execute([$userId, $provider, $providerId]);
    }

    /**
     * エラーコードを設定する
     * @param array $errorCodes エラーコード配列
     */
    public function setErrorCodes(int $errorCode): void
    {
        $this->errorCodes[] = $errorCode;
    }

    /**
     * エラーコードを取得する
     * @return array エラーコード配列
     */
    public function getErrorCodes(): array
    {
        return $this->errorCodes;
    }

    /**
     * エラーメッセージを取得する
     * @return array エラーメッセージ情報
     */
    public function errorMessages(): array
    {
        $errorCodes = array_unique($this->getErrorCodes());
        foreach ($errorCodes as $errorCode) {
            $errorMessages[] = match ($errorCode) {
                1 => "登録済みユーザーです。ログインしてください。",
                2 => "メールアドレスの形式が正しくありません。",
                3 => "メールアドレスを入力してください。",
                4 => "パスワードを入力してください。",
                5 => "8文字以上入力してください。",
                6 => "英字を1文字含めてください。",
                7 => "数字を1文字含めてください。",
                8 => "記号(@ # $ % ^ & *) を1文字含めてください。",
                9 => "メールアドレス\nまたはパスワードが正しくありません。",
            };
        }

        return $errorMessages;
    }
}
