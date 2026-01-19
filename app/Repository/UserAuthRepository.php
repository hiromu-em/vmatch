<?php
declare(strict_types=1);

namespace Repository;

class UserAuthRepository
{
    private string $errorMessage = '';

    public function __construct(private \PDO $pdo)
    {
    }

    /**
     * メールアドレスをDBに登録する
     */
    public function registerEmail(string $newEmail): void
    {
        $statement = $this->pdo->prepare("INSERT INTO users_vmatch(email) VALUES (?)");
        $statement->execute([$newEmail]);
    }

    /**
     * ユーザーIDを取得する
     */
    public function getSearchUserId(string $newEmail): string
    {
        $statement = $this->pdo->prepare("SELECT id FROM users_vmatch WHERE email = ?");
        $statement->execute([$newEmail]);
        $result = $statement->fetch();

        return $result['id'];
    }

    /**
     * メールアドレスの存在を確認する
     * @param string $email
     * @param string $authType 認証タイプ（login/register）
     * @return bool メールアドレス存在結果
     */
    public function existsByEmail(string $email, string $authType): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM users_vmatch WHERE email = ?) as status";
        $statement = $this->pdo->prepare($query);
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
     * 新規ユーザーをDBに登録する
     */
    public function registerNewUser($email, $passwordHash): void
    {
        $stetement = $this->pdo->prepare("INSERT INTO users_vmatch(email, password_hash) VALUES (?, ?)");
        $stetement->execute([$email, $passwordHash]);
    }

    /**
     * DBで管理するハッシュとパスワードの照合を行う
     * @return bool 照合結果
     */
    public function verifyPassword(string $email, string $password): bool
    {
        $statement = $this->pdo->prepare("SELECT password_hash FROM users_vmatch WHERE email = ?");
        $statement->execute([$email]);
        $result = $statement->fetch();

        if (!empty($result['password_hash']) && password_verify($password, $result['password_hash'])) {
            return true;
        } else {
            $this->setErrorMessage("メールアドレスもしくは、パスワードが正しくありません。");
            return false;
        }
    }

    /**
     * プロバイダーIDの存在確認
     * @return bool プロバイダーID存在結果
     */
    public function providerIdExists(string $providerId): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM users_vmatch_providers WHERE provider_user_id = ?) as status";
        $statement = $this->pdo->prepare($query);
        $statement->execute([$providerId]);
        $result = $statement->fetch();

        return $result['status'] ? true : false;
    }

    /**
     * プロバイダーIDとユーザーIDを紐付ける
     */
    public function linkProviderUserId(string $userId, string $providerId, string $providerName): void
    {
        $statement = $this->pdo->prepare("INSERT INTO users_vmatch_providers(user_id, provider, provider_user_id) VALUES (?, ?, ?)");
        $statement->execute([$userId, $providerName, $providerId]);
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
