<?php
declare(strict_types=1);

use Vmatch\FormValidation;

require_once __DIR__ . '/../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $formValidation = new FormValidation();

    $name = trim($_POST['user-name'] ?? '');
    $activityYoutube = isset($_POST['activity-youtube']) ? true : false;
    $activityTwitch = isset($_POST['activity-twitch']) ? true : false;
    $snsUrls['X(Twitter)'] = trim($_POST['twitter-url'] ?? '');
    $snsUrls['YouTube'] = trim($_POST['youtube-url'] ?? '');
    $snsUrls['Twitch'] = trim($_POST['twitch-url'] ?? '');

    if (isset($_FILES['profilePicture']) && !empty($_FILES['profilePicture'])) {

        // プロフィール画像の検証
        $isvalidateImage = $formValidation->validateImage($_FILES['profilePicture']);
    }

    // 他のフォームフィールドの検証は、画像検証が成功した場合にのみ実行
    if (!$isvalidateImage) {
        $formValidation->validateUserName($name);
        $formValidation->validateUrls($snsUrls);
        $formValidation->validateActivity($activityYoutube, $activityTwitch);
    }

    // エラーが存在する場合、メッセージを取得
    if ($formValidation->hasErrorMessages()) {
        $errorMessages = $formValidation->getErrorMessages();
    }

    if (empty($errorMessages)) {

    }

}

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/InitialProfileSettings.css">
    <title>Vmatch-プロフィール設定-</title>
</head>

<body>
    <header>
        <h1>Vmatch</h1>
    </header>
    <main>
        <div class="page-title">
            <h2>プロフィール設定</h2>
        </div>
        <?php if (!empty($errorMessages)): ?>
            <div class="error-messages">
                <?php foreach ($errorMessages as $message): ?>
                    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group profile-picture-group">
                <input type="file" name="profilePicture" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="user-name">ユーザー名:</label>
                <input type="text" id="user-name" name="user-name" required autocomplete="off">
            </div>
            <div class="form-group activity-section">
                <h4>活動場所:</h4>
                <div class="checkbox-item">
                    <input type="checkbox" id="activity-youtube" name="activity-youtube" checked>
                    <label for="activity-youtube">YouTube</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="activity-twitch" name="activity-twitch">
                    <label for="activity-twitch">Twitch</label>
                </div>
            </div>
            <div class="form-group sns-section">
                <h4>SNSリンク</h4>
                <div class="sns-item">
                    <label for="twitter-url">X(Twitter):</label>
                    <input type="url" id="twitter-url" name="twitter-url" placeholder="https://twitter.com/yourprofile"
                        autocomplete="off">
                </div>
                <div class="sns-item">
                    <label for="youtube-url">YouTube:</label>
                    <input type="url" id="youtube-url" name="youtube-url" placeholder="https://youtube.com/yourprofile"
                        autocomplete="off">
                </div>
                <div class="sns-item">
                    <label for="twitch-url">Twitch:</label>
                    <input type="url" id="twitch-url" name="twitch-url" placeholder="https://twitch.tv/yourprofile"
                        autocomplete="off">
                </div>
            </div>
            <div class="form-group button-group">
                <button type="submit">保存</button>
            </div>
        </form>
    </main>

</body>

</html>