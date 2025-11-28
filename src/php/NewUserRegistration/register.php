<?php

use Vmatch\NewUserRegistration\UserRegistrationService;

require_once __DIR__ . '/../../../vendor/autoload.php';

//本番環境と開発環境の分岐
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'localhost') !== false) {

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../..");
    $dotenv->load();
}

session_start([
    'use_strict_mode' => 1
]);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? null;

    $userRegistrationService = new UserRegistrationService();
    $newUser = $userRegistrationService->emailExists($email);
    $isValidEmail = $userRegistrationService->validateEmail($email);

    $errorCodes = [$newUser, $isValidEmail];
    $uniqueErrorCodes = array_unique($errorCodes);

    //メールアドレス形式OK && メールアドレス未登録ユーザーはパスワード設定画面へ移動する
    if (max($uniqueErrorCodes) === 0) {
        $userRegistrationService->registerEmail($email);

        $_SESSION['email'] = $email;
        header('Location: passwordSetting.php');
        exit;

    } else {

        $errorMessages = $userRegistrationService->registrationError($uniqueErrorCodes);
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