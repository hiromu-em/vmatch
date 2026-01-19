<?php
declare(strict_types=1);

namespace Controller;

use Core\Request;
use Core\ViewRenderer;
use Service\UserAuthentication;
use Vmatch\FormValidation;

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
     * メールアドレスの検証を行う
     */
    public function validateEmailHandle(
        ViewRenderer $viewRenderer,
        UserAuthentication $userAuthentication,
        FormValidation $formValidation
    ): void {

        $email = $this->request->input('email');


    }
}