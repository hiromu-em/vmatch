<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Vmatch\UserAuthentication\UserAuthentication;
use Vmatch\Config;

session_start([
    'use_strict_mode' => 1
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';

    // データベース接続の設定
    $config = new Config();

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $config->setHost($host);
    $databaseSettings = $config->getDatabaseSettings();

    $databaseConnection = new \PDO(
        $databaseSettings['dsn'],
        $databaseSettings['user'],
        $databaseSettings['password'],
        $databaseSettings['options']
    );

    $userAuthentication = new UserAuthentication($databaseConnection);

    // メールアドレス形式確認
    $isValidEmail = $userAuthentication->validateEmail($email);

    // 既登録ユーザー確認
    $isRegisteredUsers = $userAuthentication->emailExists($userAuthentication->getEmail());

    // サインインコード設定
    $userAuthentication->setSignInCodes($isRegisteredUsers, false);

    // メールアドレス形式OK && 未登録ユーザーの場合、登録処理へ進む
    if ($isValidEmail && !$isRegisteredUsers) {
        $_SESSION['email'] = $email;
        header('Location: newRegistration.php');
        exit;

    } else {
        // エラーメッセージを取得
        $errorMessages = $userAuthentication->errorMessages();
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