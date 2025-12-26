<?php
declare(strict_types=1);

use Vmatch\UserAuthentication\UserAuthentication;
use Vmatch\Config;

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start([
    'use_strict_mode' => 1
]);

if (isset($_GET['oauth']) && $_GET['oauth'] === 'google') {
    header('Location: ../Oauth/googleOauth.php');
    exit;
} else if (isset($_GET['oauth']) && $_GET['oauth'] === 'twitter') {
    header('Location: ../Oauth/twitterOauth.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    $config = new Config();

    // データベース接続の設定
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $config->setHost($host);
    $databaseSettings = $config->getDatabaseSettings();

    $databaseConnection = new \PDO(
        $databaseSettings['dsn'],
        $databaseSettings['user'],
        $databaseSettings['password'],
        $databaseSettings['options']
    );

    // ユーザー認証クラスのインスタンス化
    $userAuthentication = new UserAuthentication($databaseConnection);
    $validEmail = $userAuthentication->validateEmail($email);
    $validPassword = $userAuthentication->validatePassword($password);

    // メールアドレス・パスワード形式確認
    if (!$validEmail || !$validPassword) {
        $userAuthentication->setErrorCodes(9);

        // エラーメッセージ取得
        $errorMessage = array_filter($userAuthentication->errorMessages(), function ($message) {
            return $message === "メールアドレス\nまたはパスワードが正しくありません。";
        });
    }


    if (empty($errorMessage)) {

        // DBにメールアドレス・パスワードが存在するか確認
        $existingUsersEmail = $userAuthentication->emailExists($email);
        $existingUsersPassword = $userAuthentication->verifyPassword($email, $password);

        // 認証済みユーザー情報設定またはエラーコード設定
        $existingUsersEmail && $existingUsersPassword === true
            ? $userAuthentication->setAuthenticatedUser($email, $password) : $userAuthentication->setErrorCodes(9);

        // エラーメッセージ取得
        if (!empty($userAuthentication->getErrorCodes())) {
            $errorMessage = $userAuthentication->errorMessages();
        }
    }

    // 認証済みユーザー情報取得
    $authenticatedUser = $userAuthentication->getAuthenticatedUser() ?? '';

    if (empty($errorMessage) && $authenticatedUser !== '') {
        // セッションに認証済みユーザー情報を保存
        $_SESSION['authenticatedUser'] = $authenticatedUser;

        // ダッシュボードへリダイレクト
        header('Location: ../dashboard.php');
        exit;

    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&family=Poppins:wght@600&display=swap"
        rel="stylesheet">
    <title>Vmatch-ログイン-</title>
</head>

<body>
    <div class="login-hero-background"></div>
    <header class="login-header">
        <h2 class="login-logo"><a href="../../../index.php">Vmatch</a></h2>
    </header>
    <main class="login-container">
        <div class="login-hero-content">
            <h1 class="login-main-title">ログイン</h1>
        </div>
        <?php if (!empty($errorMessage)): ?>
            <div class="error-messages-container">
                <div class="error-item">
                    <p><?php echo nl2br(htmlspecialchars(array_pop($errorMessage), ENT_QUOTES, 'UTF-8')); ?></p>
                </div>
            </div>
        <?php endif; ?>
        <form method="post" class="login-credentials-form">
            <div class="login-form-group">
                <label for="email" class="login-form-label">メールアドレス</label>
                <input type="email" id="email" name="email" class="login-form-input" autocomplete="off" required
                    placeholder="example@email.com">
            </div>
            <div class="login-form-group">
                <label for="password" class="login-form-label">パスワード</label>
                <input type="password" id="password" name="password" class="login-form-input" autocomplete="off"
                    required placeholder="パスワードを入力">
            </div>
            <button type="submit" class="login-submit-button">ログイン</button>
        </form>

        <div class="login-divider">
            <span class="login-divider-text">または</span>
        </div>

        <!-- OAuth ログイン -->
        <form method="get" class="login-oauth-form">
            <button class="login-gsi-material-button" type="submit" name="oauth" value="google">
                <div class="login-gsi-material-button-state"></div>
                <div class="login-gsi-material-button-content-wrapper">
                    <div class="login-gsi-material-button-icon">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"
                            xmlns:xlink="http://www.w3.org/1999/xlink" style="display: block;">
                            <path fill="#EA4335"
                                d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z">
                            </path>
                            <path fill="#4285F4"
                                d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z">
                            </path>
                            <path fill="#FBBC05"
                                d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z">
                            </path>
                            <path fill="#34A853"
                                d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z">
                            </path>
                            <path fill="none" d="M0 0h48v48H0z"></path>
                        </svg>
                    </div>
                    <span class="login-gsi-material-button-contents">Sign in with Google</span>
                </div>
            </button>
            <button class="login-gsi-material-button login-x-material-button" type="submit" name="oauth"
                value="twitter">
                <div class="login-gsi-material-button-state"></div>
                <div class="login-gsi-material-button-content-wrapper">
                    <div class="login-gsi-material-button-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" aria-hidden="true">
                            <path
                                d="M20.285 7.715a1 1 0 0 0-1.414-1.414L12 13.172 5.129 6.301A1 1 0 1 0 3.715 7.715L10.586 14.586 3.715 21.457a1 1 0 0 0 1.414 1.414L12 15.999l6.871 6.872a1 1 0 0 0 1.414-1.414L13.414 14.586l6.871-6.871z" />
                        </svg>
                    </div>
                    <span class="login-gsi-material-button-contents">Sign in with X</span>
                </div>
            </button>
        </form>
        <div class="login-auth-link">
            <p>アカウントをお持ちでない方は<a href="register.php">新規登録</a></p>
        </div>
    </main>
</body>

</html>