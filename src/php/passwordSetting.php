<?php

require_once __DIR__ . '/../../vendor/autoload.php';

//本番環境と開発環境の分岐
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'localhost') !== false) {

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
    $dotenv->load();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password']?? null;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vmatch-パスワード設定-</title>
</head>

<body>
    <h1>パスワード設定</h1>
    <?php if (!empty($errorMessage)): ?>
        <div class="container-error-message">
            <p><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    <?php endif; ?>
    <form method="post">
        <p>英数字記号(@#$%^&*)含めて8文字以上で設定してください。</p>
        <label for="password">パスワード</label>
        <input type="password" id="password" name="password" required autocomplete="off">
        <button type="submit">送信</button>
    </form>
</body>

</html>