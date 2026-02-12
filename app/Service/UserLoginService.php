<?php
declare(strict_types=1);

namespace Service;

use Repository\UserAuthRepository;
use Vmatch\Exception\DatabaseException;
use Vmatch\Result;

class UserLoginService
{
    public function __construct(private UserAuthRepository $authRepository)
    {
    }

    /**
     * ユーザーのログインを実行する
     */
    public function executeUserLogin(string $email, string $password): Result
    {
        try{
            $userRecord = $this->authRepository->findUserRecord($email);
        } catch (\PDOException $e){
            throw new DatabaseException();
        }

        if (empty($userRecord)) {
            return Result::failure("メールアドレスもしくは\nパスワードが正しくありません。");
        }

        if (!password_verify($password, $userRecord['password_hash'])) {
            return Result::failure("メールアドレスもしくは\nパスワードが正しくありません。");
        }

        return Result::success($userRecord['id']);
    }
}