<?php
declare(strict_types=1);

namespace Repository;

class UserAuthRepository
{
    public function __construct(private \PDO $pdo)
    {
    }

    /**
     * ユーザーレコードを取得する
     */
    public function findUserRecord(string $email): array
    {
        $statement = $this->pdo->prepare("SELECT * FROM users_vmatch WHERE email = ?");
        $statement->execute([$email]);
        $result = $statement->fetch();

        return $result ?: [];
    }

    /**
     * メールアドレスの存在を確認する
     */
    public function existsByEmail(string $email): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM users_vmatch WHERE email = ?) AS email_exists";
        $statement = $this->pdo->prepare($query);
        $statement->execute([$email]);

        $result = $statement->fetch();

        return $result['email_exists'];
    }

    /**
     * 新規ユーザーレコードを取得する
     */
    public function fetchNewUserRecord($email, $passwordHash = null): array
    {
        $stetement = $this->pdo->prepare(
            "INSERT INTO users_vmatch(email, password_hash) VALUES (?, ?) RETURNING *"
        );
        $stetement->execute([$email, $passwordHash]);
        $userRecord = $stetement->fetch();

        return $userRecord;
    }

    /**
     * プロバイダーIDの存在確認
     * @return bool プロバイダーID存在結果
     */
    public function providerIdExists(string $providerId): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM users_vmatch_providers WHERE provider_id = ?) as status";
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
        $statement = $this->pdo->prepare("INSERT INTO users_vmatch_providers(id, provider_name, provider_id) VALUES (?, ?, ?)");
        $statement->execute([$userId, $providerName, $providerId]);
    }
}
