<?php

    use Vmatch\Validation;
    use Vmatch\certification\RegisterMail;
    use Vmatch\UserRegistrationService;

    require_once __DIR__ . '/../../vendor/autoload.php';

    //本番環境と開発環境の分岐
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if(strpos($host, 'localhost') !== false) {

        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
        $dotenv->load();
    }


    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $newUserEmail = $_POST['email'] ?? null;

        //メールの形式を確認
        $validation = new Validation();
        $isValidEmail = $validation -> ValidationCheck($newUserEmail);
        
        $userRegistrationService = new UserRegistrationService();
        $userRegistrationService -> isEmailRegistered($newUserEmail);
        
        if($isValidEmail){
            $userRegistrationService -> registerTemporaryUser($newUserEmail);

            $registerMail = new RegisterMail();
            $registerMail -> send($newUserEmail);
            
        }else{
            $emailErrorMessage = "正しい形式で入力してください。";
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
    <?php if(!empty($emailErrorMessage)): ?>
        <div class="container-error-message">
            <p><?php echo $emailErrorMessage; ?></p>
        </div>
    <?php endif ?>
    <form method="post">
        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" placeholder="sample@example.com" required autocomplete="off">
        <button type="submit">送信</button>
    </form>
</body>
</html>
