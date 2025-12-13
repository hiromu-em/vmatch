<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Vmatch\UserAuthentication\UserAuthentication;

session_start([
    'use_strict_mode' => 1
]);

if ($_SESSION['email'] === null || !isset($_SESSION['email'])) {
    header('Location: /');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // セッションからメールアドレスを取得
    // もしセッションにメールアドレスが無ければトップページへリダイレクト
    $email = $_SESSION['email'] ?? header('Location: /');

    $password = $_POST['password'] ?? null;

    $userAuthentication = new UserAuthentication();
    $isValidPassword = $userAuthentication->validatePassword($password);

    // パスワード形式確認
    if (!$isValidPassword) {
        $errorMessages = $userAuthentication->errorMessages();
    } else {

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $userAuthentication->userRegistration($email, $passwordHash);
        header('Location: profileSetting.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/userSetting.css">
    <title>Vmatch-パスワード設定-</title>
</head>

<body>
    <div class="password-setting-title">
        <h3>パスワード設定</h3>
    </div>
    <div class="password-setting-container">
        <h4>メールアドレス：<?php echo htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8'); ?></h4>
        <?php if (!empty($errorMessages)): ?>
            <div class="error-messages-container">
                <?php foreach ($errorMessages as $message): ?>
                    <div class="error-item">
                        <p><?php echo nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group" style="text-align: start;">
                <label for="password">パスワード</label>
            </div>
            <input type="password" id="password" name="password" placeholder="英数字記号(@#$%&*_!)含めて8文字以上" required
                autocomplete="off" size="33">
            <button type="submit">送信</button>
        </form>
    </div>
</body>

</html>