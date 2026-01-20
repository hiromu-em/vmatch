<?php
declare(strict_types=1);

namespace Service;

use Repository\UserAuthRepository;

class RegisterService
{
    public function __construct(private UserAuthRepository $authRepository)
    {
    }

    public function executeEmailSearch($email)
    {
    }
}