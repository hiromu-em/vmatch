<?php
declare(strict_types=1);

namespace Controller;

use Core\Request;
use Core\ViewRenderer;
use Vmatch\FormValidation;
use Service\UserRegister;

class AuthController
{
    public function __construct(private Request $request)
    {
    }

    public function showLoginForm(ViewRenderer $viewRenderer): void
    {
        $viewRenderer->render('login');
    }

    public function showRegisterForm(ViewRenderer $viewRenderer): void
    {
        $viewRenderer->render('register');
    }

    /**
     * ユーザーの新規登録を行う
     */
    public function registerHandle(FormValidation $formValidation, ViewRenderer $viewRenderer, UserRegister $userRegister): void
    {
        $email = $this->request->input('email');
        $formValidation->validateEmail($email);

        if ($formValidation->hasErrorMessages()) {
            $viewRenderer->render('register', [
                'error' => $formValidation->getErrorMessage()
            ]);
        }

        if ($userRegister->isEmailRegistered($email)) {
            $viewRenderer->render('register', [
                'error' => $userRegister->getErrorMessage()
            ]);
        }
    }
}