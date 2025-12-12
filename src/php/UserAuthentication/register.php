<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Vmatch\UserAuthentication\UserAuthentication;

session_start([
    'use_strict_mode' => 1
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? null;

    $userAuthentication = new UserAuthentication();
    $isNewUser = $userAuthentication->emailExists($email);
    $isValidEmail = $userAuthentication->validateEmail(trim($email));

    $errorCodes = [$isNewUser, $isValidEmail];

    // メールアドレス形式&&パスワード形式確認
    if (max($errorCodes) === 0) {

        $userAuthentication->registerEmail($email);
        $_SESSION['email'] = $email;
        header('Location: passwordSetting.php');
        exit;

    } else {

        $errorMessages = $userAuthentication->registrationErrorMessage(array_unique($errorCodes));
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vmatch-新規登録-</title>
    <!-- 既存のグローバルスタイルと register 用スタイルを読み込む -->
    <link rel="stylesheet" href="../../css/index.css">
    <link rel="stylesheet" href="../../css/register.css">
</head>

<body>
    <div class="hero-background"></div>

    <header>
        <div class="logo"><a href="/">Vmatch</a></div>
    </header>

    <main class="container">
        <section class="hero-content register-card">
            <h1 class="main-title">新規登録</h1>

            <?php if (!empty($errorMessages)): ?>
                <div class="error-messages-container">
                    <?php foreach ($errorMessages as $message): ?>
                        <div class="error-item">
                            <p><?php echo nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="auth-form" novalidate>
                <div class="register-form-group">
                    <label for="email" class="input-label">メールアドレス</label>
                </div>
                <input type="email" id="email" name="email" placeholder="sample@example.com" required autocomplete="off"
                    class="text-input">
                <button type="submit" class="btn btn-primary submit-btn">送信</button>
            </form>

            <p class="subnote">既にアカウントをお持ちの方は <a href="login.php" class="link">ログイン</a></p>
        </section>
    </main>

</body>

</html>