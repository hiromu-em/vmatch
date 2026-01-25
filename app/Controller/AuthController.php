<?php
declare(strict_types=1);

namespace Controller;

use Core\Request;
use Core\Response;
use Core\ViewRenderer;
use Core\Session;
use Service\RegisterService;
use Vmatch\FormValidation;

class AuthController
{
    public function __construct(
        private Request $request,
        private Response $response,
        private Session $session
    ) {
    }

    public function showLoginForm(ViewRenderer $viewRenderer): void
    {
        $viewRenderer->render('login');
    }

    public function showRegisterForm(ViewRenderer $viewRenderer): void
    {
        $viewRenderer->render(
            'register',
            ['error' => $this->session->getOnce('errorMessage')]
        );
    }

    public function showNewPasswordSetting(ViewRenderer $viewRenderer): void
    {
        $viewRenderer->render(
            'signUp',
            ['email' => $this->session->get('email')]
        );
    }

    /**
     * 新規登録用のメールアドレス検証を行う
     */
    public function validateNewRegisterEmail(
        RegisterService $registerService,
        FormValidation $formValidation
    ): void {
        $email = $this->request->input('email');

        $emailFormatResult = $formValidation->validateEmailFormat($email);
        if (!$emailFormatResult->isSuccess()) {

            $this->session->set('errorMessage', $emailFormatResult->error());
            $this->response->redirect('/register');
        }

        $canRegisterResult = $registerService->canRegisterByEmail($email);
        if (!$canRegisterResult->isSuccess()) {

            $this->session->set('errorMessage', $canRegisterResult->error());
            $this->response->redirect('/register');
        }

        $token = $registerService->generateCertificationToken();

        $this->session->set('token', $token);
        $this->session->set('email', $email);

        $this->response->redirect("/newPasswordSetting?token=$token");
    }
}