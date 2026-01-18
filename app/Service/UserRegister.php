<?php
declare(strict_types=1);

namespace Service;

use Model\UserAuthentication;

/**
 * ユーザー登録を行うクラス
 */
class UserRegister
{

    public function __construct(private UserAuthentication $userAuthentication)
    {
    }

    public function register(string $email)
    {

    }

    public function isEmailRegistered()
    {

    }
}