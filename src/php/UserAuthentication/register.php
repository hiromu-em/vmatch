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
    $newUser = $userAuthentication->emailExists($email);
    $isValidEmail = $userAuthentication->validateEmail($email);

    $errorCodes = [$newUser, $isValidEmail];
    $uniqueErrorCodes = array_unique($errorCodes);

    //メールアドレス形式OK && メールアドレス未登録ユーザーはパスワード設定画面へ移動する
    if (max($uniqueErrorCodes) === 0) {
        $userAuthentication->registerEmail($email);

        $_SESSION['email'] = $email;
        header('Location: passwordSetting.php');
        exit;

    } else {

        $errorMessages = $userAuthentication->registrationError($uniqueErrorCodes);
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vmatch-新規登録-</title>
</head>

<body>
    <h1>新規登録</h1>
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
        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" placeholder="sample@example.com" required autocomplete="off">
        <button type="submit">送信</button>
    </form>
</body>

</html>