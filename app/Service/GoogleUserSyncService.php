<?php
declare(strict_types=1);

namespace Service;

use Repository\UserAuthRepository;
use Entity\User;

class GoogleUserSyncService
{

    public function __construct(private UserAuthRepository $authRepository)
    {
    }

    public function synchronizeUserData(string $providerId, string $email): User
    {
        if ($this->authRepository->providerIdExists($providerId)) {

            $userRecord = $this->authRepository->findUserRecordByProviderId($providerId);

            return new User(
                userId: $userRecord['id'],
                email: $userRecord['email'],
                isNewUser: false,
                providerId: $providerId,
                providerName: 'Google'
            );
        }

        $userRecord = $this->authRepository->fetchNewUserRecord($email);
        $this->authRepository->linkProviderUserId($userRecord['id'], $providerId, 'Google');

        return new User(
            userId: $userRecord['id'],
            email: $userRecord['email'],
            isNewUser: true,
            providerId: $providerId,
            providerName: 'Google'
        );
    }

}