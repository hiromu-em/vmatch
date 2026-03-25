<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vminder-ダッシュボード-</title>
</head>

<body>
    <header id="header">
        <h1><a href="/dashboard">Vminder</a></h1>
        <form action="/logout" method="get">
            <button type="submit" class="logout-button">ログアウト</button>
        </form>
    </header>
    <main class="vtuber-reminder-container">
        <div class="group-section">
            <div class="hololive-group-tab">
                <h3>ホロライブ</h3>
            </div>
        </div>
        <div class="search-section">
            <input type="search" id="member-search-box" placeholder="メンバー検索" autocomplete="off">
        </div>
        <div class="reminder-select-list">

            <?php foreach ($vtuberChannels as $vtuberChannel): ?>
                <form action="/reminder-register" method="post" id="reminder-select-checkbox">
                    <div class="reminder-card-section">
                        <img src="<?= htmlspecialchars($vtuberChannel['thumbnail_url']) ?>"
                            alt="<?= htmlspecialchars($vtuberChannel['name']) ?>">
                        <p class="vtuber-name-content"><?= htmlspecialchars($vtuberChannel['name']) ?></p>
                        <input type="checkbox" name="selected_members[]"
                            value="<?= htmlspecialchars($vtuberChannel['channel_id']) ?>">
                    </div>
                </form>
            <?php endforeach ?>

        </div>
    </main>

</body>

</html>