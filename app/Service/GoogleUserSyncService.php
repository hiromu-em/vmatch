<?php
declare(strict_types=1);

namespace Service;

use Repository\UserAuthRepository;

class GoogleUserSyncService
{

    public function __construct(private UserAuthRepository $authRepository)
    {
    }

    public function synchronizeUserData(string $id, string $email)
    {

    }

}