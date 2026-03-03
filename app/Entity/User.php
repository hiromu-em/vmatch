<?php
declare(strict_types=1);

namespace Entity;

final class User
{
    public function __construct(
        private string $userId,
        private string $email,
        private bool $isNewUser,
        private ?string $providerId,
        private ?string $providerName
    ) {
    }

    public function isNewUser(): bool
    {
        return $this->isNewUser;
    }

    public function getUserRecord(): array
    {
        return [
            'user_id' => $this->userId,
            'email' => $this->email,
            'provider_id' => $this->providerId,
            'provider_name' => $this->providerName
        ];
    }
}