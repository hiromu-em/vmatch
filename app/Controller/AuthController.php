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
            ['error' => $this->session->getOnceStr('errorMessage')]
        );
    }

    public function showNewPasswordSetting(ViewRenderer $viewRenderer): void
    {
        $viewRenderer->render(
            'signUp',
            [
                'email' => $this->session->get('email'),
                'errors' => $this->session->getOnceArray('errorMessages')
            ]
        );
    }

    /**
     * トークンを検証して成否を処理する</br>
     * 成功→パスワード設定にリダイレクト</br>
     * 失敗→エラーメッセージを表示
     */
    public function handleTokenVerification(RegisterService $registerService): never
    {
        $verificationToken = $this->request->fetchInputStr('token');

        $verificationTokenResult = $registerService->validateCertificationToken(
            $verificationToken,
            $this->session->get('token')
        );

        if (!$verificationTokenResult->isSuccess()) {

            $this->session->setStr('errorMessage', $verificationTokenResult->error());
            $this->response->redirect('/register', 301);
        }

        $this->session->remove('token');

        $this->response->redirect('/new-password-setting', 301);
    }

    /**
     * 新規登録用のメールアドレスを検証して成否を処理する</br>
     * 成功: 認証トークンを生成してリダイレクト</br>
     * 失敗: エラーメッセージを表示
     */
    public function handleRegisterEmailVerification(
        RegisterService $registerService,
        FormValidation $formValidation
    ): never {

        $email = $this->request->fetchInputStr('email');
        $emailFormatResult = $formValidation->validateEmailFormat($email);

        if (!$emailFormatResult->isSuccess()) {

            $this->session->setStr('errorMessage', $emailFormatResult->error());
            $this->response->redirect('/register');
        }

        $canRegisterEmailResult = $registerService->canRegisterByEmail($email);

        if (!$canRegisterEmailResult->isSuccess()) {

            $this->session->setStr('errorMessage', $canRegisterEmailResult->error());
            $this->response->redirect('/register');
        }

        $this->session->setStr('email', $email);

        $token = $registerService->generateCertificationToken();
        $this->session->setStr('token', $token);

        $this->response->redirect("/token-verification?token=$token");
    }

    /**
     * 新規ユーザー登録を処理する
     */
    public function handleNewUserRegister(
        RegisterService $registerService,
        FormValidation $formValidation
    ) {

        $email = $this->session->get('email');
        $plainPassword = $this->request->fetchInputStr('password');

    }
}