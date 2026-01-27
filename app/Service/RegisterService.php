<?php
declare(strict_types=1);

namespace Service;

use Repository\UserAuthRepository;
use Vmatch\Result;

class RegisterService
{
    public function __construct(private UserAuthRepository $authRepository)
    {
    }

    /**
     * メールアドレスとして登録が可能か確認をする
     */
    public function canRegisterByEmail($email): Result
    {
        if ($this->authRepository->existsByEmail($email)) {
            Result::failure("登録済みユーザーです。\nログインしてください");
        }

        return Result::success();
    }

    /**
     * 認証トークンを生成する
     */
    public function generateCertificationToken(): string
    {
        return bin2hex(random_bytes(12));
    }
}