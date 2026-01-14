<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vmatch-新規登録-</title>
    <link rel="stylesheet" href="/resources/css/index.css">
    <link rel="stylesheet" href="/resources/css/register.css">
</head>

<body>
    <div class="hero-background"></div>

    <header>
        <div class="logo"><a href="/">Vmatch</a></div>
    </header>

    <main class="container">
        <section class="hero-content register-card">
            <h1 class="main-title">新規登録</h1>

            <?php if (!empty($errorMessage)): ?>
                <div class="error-messages-container">
                    <div class="error-item">
                        <p><?php echo nl2br(htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form method="post" action="/register" class="auth-form" novalidate>
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