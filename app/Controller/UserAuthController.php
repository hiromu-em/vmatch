<?php
declare(strict_types=1);

namespace Controller;

use Core\Request;
use Core\Response;
use Core\ViewRenderer;
use Core\Session;
use Service\UserRegisterService;
use Service\UserLoginService;
use Vmatch\FormValidation;
use Vmatch\Exception\DatabaseException;

class UserAuthController
{
    public function __construct(
        private Request $request,
        private Response $response,
        private Session $session
    ) {
    }

    public function showLoginForm(ViewRenderer $viewRenderer): void
    {
        $viewRenderer->render(
            'login',
            ['error' => $this->session->getOnceStr('errorMessage')]
        );
    }

    public function showRegisterForm(ViewRenderer $viewRenderer): void
    {
        $viewRenderer->render(
            'register',
            ['error' => $this->session->getOnceStr('errorMessage')]
        );
    }

    public function showNewPasswordSetting(ViewRenderer $viewRenderer): never
    {
        $tokenStatus = $this->session->getArray('token_status');

        if (\array_key_exists('consumed', $tokenStatus)) {

            if ($tokenStatus['consumed'] === true) {

                $viewRenderer->render(
                    'signUp',
                    [
                        'email' => $this->session->getStr('email'),
                        'errors' => $this->session->getOnceArray('errorMessages')
                    ]
                );
            }
        }

        // トークンを検証していない場合、top画面へリダイレクト
        $this->response->redirect('/', 301);
    }

    /**
     * トークンを検証して成否を処理する</br>
     * 成功→パスワード設定にリダイレクト</br>
     * 失敗→エラーメッセージを表示
     */
    public function handleTokenVerification(UserRegisterService $registerService): never
    {
        // SESSIONに存在しない場合はTOP画面にリダイレクト
        if (!$this->session->has('token')) {
            $this->response->redirect('/', 301);
        }

        $verificationTokenResult = $registerService->validateCertificationToken(
            $this->request->fetchInputStr('token'),
            $this->session->getStr('token')
        );

        if (!$verificationTokenResult->isSuccess()) {

            $this->session->setStr('errorMessage', $verificationTokenResult->error());
            $this->response->redirect('/register', 301);
        }

        $this->session->setArray('token_status', ['consumed' => true]);

        $this->response->redirect('/new-password-setting', 301);
    }

    /**
     * 新規登録用のメールアドレスを検証して成否を処理する</br>
     * 成功: 認証トークンを生成してリダイレクト</br>
     * 失敗: エラーメッセージを表示
     */
    public function handleRegisterEmailVerification(
        UserRegisterService $registerService,
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
     * 新規ユーザー登録の処理をする
     */
    public function handleNewUserRegister(
        UserRegisterService $registerService,
        FormValidation $formValidation,
        ViewRenderer $viewRenderer
    ): never {

        $plainPassword = $this->request->fetchInputStr('password');
        $email = $this->session->getStr('email');

        $passwordFormatResult = $formValidation->validatePasswordFormat($plainPassword);

        if (!$passwordFormatResult->isSuccess()) {
            $this->session->setArray('errorMessages', $passwordFormatResult->error());
            $this->response->redirect('/new-password-setting');
        }

        $hashPassword = $registerService->generatePasswordHash($plainPassword);

        try {
            $userId = $registerService->executeRegisterNewUser($email, $hashPassword);

        } catch (DatabaseException $e) {
            http_response_code(500);
            $viewRenderer->render('systemError');
        }

        // 登録成功後、不要なSession情報をクリア
        $this->session->clear();

        $this->session->setStr('user_id', $userId);

        $this->response->redirect('/init-profile-setting');
    }

    /**
     * ユーザーのログインを処理する
     */
    public function handleUserLogin(
        FormValidation $formValidation,
        UserLoginService $loginService,
        ViewRenderer $viewRenderer
    ): void {
        $email = $this->request->fetchInputStr('email');
        $plainPassword = $this->request->fetchInputStr('password');

        $emailFormatResult = $formValidation->validateEmailFormat($email);
        $passwordFormatResult = $formValidation->validatePasswordFormat($plainPassword);

        if (!$emailFormatResult->isSuccess() && !$passwordFormatResult->isSuccess()) {
            $this->session->setStr('errorMessage', "メールアドレスもしくは、\nパスワードが正しくありません。");
            $this->response->redirect('/login');
        }

        try {
            $executeUserLoginResult = $loginService->executeUserLogin($email, $plainPassword);

        } catch (DatabaseException $e) {
            http_response_code(500);
            $viewRenderer->render('systemError');
        }

        if (!$executeUserLoginResult->isSuccess()) {
            $this->session->setStr('errorMessage', $executeUserLoginResult->error());
            $this->response->redirect('/login');
        }

        $userId = $executeUserLoginResult->value();
    }
}