<?php
declare(strict_types=1);

namespace Service;

use Repository\UserAuthRepository;
use Vmatch\Exception\DatabaseException;

class UserLoginService
{
    public function __construct(private UserAuthRepository $authRepository)
    {
    }

    
}