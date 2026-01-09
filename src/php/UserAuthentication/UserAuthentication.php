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

    // エラーメッセージ
    private string $errorMessage = '';

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
     * メールアドレスの存在を確認する<br>
     * @param string $email メールアドレス
     * @param string $authType 認証タイプ（login/register）
     * @return bool メールアドレス存在結果
     */
    public function existsByEmail(string $email, string $authType): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM users_vmatch WHERE email = ?) as status";
        $statement = $this->databaseConnection->prepare($query);
        $statement->execute([$email]);

        $result = $statement->fetch();

        if ($result['status'] === true && $authType === 'register') {
            $this->setErrorMessage("登録済みユーザーです。ログインしてください。");
            return true;

        } elseif ($result['status'] === false && $authType === 'login') {
            $this->setErrorMessage("メールアドレスもしくは、パスワードが正しくありません。");
            return false;
        }

        return $result['status'] ? true : false;
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
     * パスワードの照合を行う
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
    
    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
