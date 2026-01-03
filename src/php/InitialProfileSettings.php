<?php
declare(strict_types=1);

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
        <form method="post" enctype="multipart/form-data">
            <div class="form-group profile-picture-group">
                <input type="file" name="profilePicture" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="user-name">名前:</label>
                <input type="text" id="user-name" name="user-name" required>
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
                    <input type="url" id="twitter-url" name="twitter-url" placeholder="https://twitter.com/yourprofile">
                </div>
                <div class="sns-item">
                    <label for="youtube-url">YouTube:</label>
                    <input type="url" id="youtube-url" name="youtube-url" placeholder="https://youtube.com/yourprofile">
                </div>
                <div class="sns-item">
                    <label for="twitch-url">Twitch:</label>
                    <input type="url" id="twitch-url" name="twitch-url" placeholder="https://twitch.tv/yourprofile">
                </div>
            </div>
            <div class="form-group button-group">
                <button type="submit">保存</button>
            </div>
        </form>
    </main>

</body>

</html>