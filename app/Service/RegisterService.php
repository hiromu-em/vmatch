<?php
declare(strict_types=1);

namespace Service;

use Repository\UserAuthRepository;
use Vmatch\Result;
use Vmatch\Exception\DatabaseException;

class RegisterService
{
    public function __construct(private UserAuthRepository $authRepository)
    {
    }

    /**
     * 新規ユーザーとしてDBに登録する
     * @throws DatabaseException
     */
    public function registerNewUser($email, $hashPassword): void
    {
        try {
            $this->authRepository->insertNewUser($email, $hashPassword);
        } catch (\PDOException $e) {
            throw new DatabaseException();
        }
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

    /**
     * ハッシュ化したパスワードを生成する
     */
    public function generatePasswordHash(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    /**
     * 認証トークンを検証する
     */
    public function validateCertificationToken(string $verificationToken, string $token): Result
    {
        if ($verificationToken !== $token) {
            return Result::failure("トークンの検証に失敗しました。\n再度新規登録してください");
        }

        return Result::success();
    }
}