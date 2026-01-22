<?php
declare(strict_types=1);

namespace Controller;

use Core\Request;
use Core\Response;
use Core\ViewRenderer;
use Service\RegisterService;
use Vmatch\FormValidation;

class AuthController
{
    public function __construct(private Request $request, private Response $response)
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
     * 新規登録用のメールアドレス検証を行う
     */
    public function validateNewRegisterEmail(
        ViewRenderer $viewRenderer,
        RegisterService $registerService,
        FormValidation $formValidation
    ): void {
        $email = $this->request->input('email');

        $emailFormatResult = $formValidation->validateEmailFormat($email);
        if (!$emailFormatResult->isSuccess()) {

            $viewRenderer->render(
                'register',
                $emailFormatResult->errorMessages()
            );
            
            return;
        }

        $canRegisterResult = $registerService->canRegisterByEmail($email);
        if ($canRegisterResult->isSuccess()) {

        }
    }
}