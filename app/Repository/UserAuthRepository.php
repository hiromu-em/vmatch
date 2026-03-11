<?php
declare(strict_types=1);

namespace Repository;

class UserAuthRepository
{
    public function __construct(private \PDO $pdo)
    {
    }

    /**
     * メールアドレスからユーザーレコードを取得する
     */
    public function findUserRecordByEmail(string $email): array
    {
        $statement = $this->pdo->prepare(
            "SELECT * FROM users LEFT JOIN users_provider USING(id) 
            WHERE users.email = ? AND provider_id IS NULL"
        );
        $statement->execute([$email]);
        $result = $statement->fetch();

        return $result ?: [];
    }

    /**
     * プロパイダ―IDからユーザーレコードを取得する
     */
    public function findUserRecordByProviderId($providerId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT * FROM users LEFT JOIN users_provider USING(id) WHERE provider_id = ?"
        );
        $statement->execute([$providerId]);
        $result = $statement->fetch();

        return $result ?: [];
    }

    /**
     * メールアドレスの存在を確認する
     */
    public function existsByEmail(string $email): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM users WHERE email = ?) AS email_exists";
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
            "INSERT INTO users(email, password_hash) VALUES (?, ?) RETURNING *"
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
        $query = "SELECT EXISTS(SELECT 1 FROM users_provider WHERE provider_id = ?) as status";
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
        $statement = $this->pdo->prepare("INSERT INTO users_provider(id, provider_name, provider_id) VALUES (?, ?, ?)");
        $statement->execute([$userId, $providerName, $providerId]);
    }
}
