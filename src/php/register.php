<?php

use Vmatch\UserRegistrationService;

require_once __DIR__ . '/../../vendor/autoload.php';

//本番環境と開発環境の分岐
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'localhost') !== false) {

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
    $dotenv->load();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? null;

    $userRegistrationService = new UserRegistrationService();
    $newUser = $userRegistrationService->emailExists($email);
    $isValidEmail = $userRegistrationService->validateEmail($email);

    //メールアドレス形式OK && 未登録ユーザーは新規登録する
    if ($isValidEmail['validation_check'] && !$newUser['status']) {
        $userRegistrationService->registerEmail($email);

    } else {
        $errorCodeArray = [
            'register_user' => $newUser['error_code'],
            'email_validation' => $isValidEmail['error_code'],
        ];

        $errorMessage = $userRegistrationService->registrationError($errorCodeArray);
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vmatch</title>
</head>

<body>
    <h1>新規登録</h1>
    <?php if (!empty($errorMessage)): ?>
        <div class="container-error-message">
            <p><?php echo nl2br(htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8')); ?></p>
        </div>
    <?php endif; ?>
    <form method="post">
        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" placeholder="sample@example.com" required autocomplete="off">
        <button type="submit">送信</button>
    </form>
</body>

</html>