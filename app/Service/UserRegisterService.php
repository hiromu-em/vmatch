<?php
declare(strict_types=1);

namespace Service;

use Repository\UserAuthRepository;
use Vmatch\Result;
use Vmatch\Exception\DatabaseException;

class UserRegisterService
{
    public function __construct(private UserAuthRepository $authRepository)
    {
    }

    /**
     * 新規ユーザーをDBに登録する処理を実行する。</br>
     * 登録が正常に完了した場合、ユーザーレコードを返す。
     * @throws DatabaseException
     */
    public function executeRegisterNewUser($email, $hashPassword): array
    {
        try {
            return $this->authRepository->fetchNewUserRecord($email, $hashPassword);
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
            return Result::failure("登録済みユーザーです。\nログインしてください");
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
     * Requestから受け取った認証トークンを検証する
     * @param string $verificationToken GETパラメーターから受け取った認証トークン
     * @param string $token Sessionに保存したトークン 
     */
    public function validateCertificationToken(string $verificationToken, string $token): Result
    {
        if ($verificationToken !== $token) {
            return Result::failure("トークンの検証に失敗しました。\n再度新規登録をしてください");
        }

        return Result::success();
    }
}