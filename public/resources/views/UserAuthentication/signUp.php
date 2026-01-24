<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/resources/css/userSetting.css">
    <title>Vmatch-パスワード設定-</title>
</head>

<body>
    <div class="password-setting-title">
        <h3>パスワード設定</h3>
    </div>
    <div class="password-setting-container">
        <h4>メールアドレス：<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></h4>
        <?php if (!empty($errors)): ?>
            <div class="error-messages-container">
                <?php foreach ($errors as $error): ?>
                    <div class="error-item">
                        <p><?php echo nl2br(htmlspecialchars($error, ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="/rgister">
            <div class="form-group" style="text-align: start;">
                <label for="password">パスワード</label>
            </div>
            <input type="password" id="password" name="password" placeholder="英数字記号(@#$%&*_!)含めて8文字以上"
                autocomplete="off" size="33">
            <button type="submit">送信</button>
        </form>
    </div>
</body>

</html>