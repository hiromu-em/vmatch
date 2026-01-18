<?php
declare(strict_types=1);

namespace Service;

use Model\UserAuthenticationManager;
use Vmatch\FormValidation;

class UserAuthenticationService
{
    public function __construct(private UserAuthenticationManager $userAuthManager, private FormValidation $formValidation)
    {
    }

    public function register(string $email)
    {
    }

    /**
     * メールアドレスの検証を実行する。</br>
     * メール形式の検証とメールアドレスがDBに存在するか検証する
     */
    public function executeEmailValidation(string $email, string $authType): bool
    {
        $this->formValidation->validateEmail($email);

        if ($this->formValidation->hasErrorMessages()) {
            $this->getErrorMessage($this->formValidation->getErrorMessage());
            return false;
        }

        $isExistsByEmail = $this->userAuthentication->existsByEmail($email, $authType);

        if ($isExistsByEmail) {
            $this->getErrorMessage($this->userAuthentication->getErrorMessage());
            return false;
        }

        return true;
    }

    public function getErrorMessage(string $message): string
    {
        return $message;
    }
}