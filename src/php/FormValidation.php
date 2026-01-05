<?php
declare(strict_types=1);

namespace Vmatch;

class FormValidation
{

    private array $errorMessages = [];

    /**
     * プロフィール写真を検証する。
     * @param array $profilePicture アップロードされたプロフィール画像の情報
     * @return bool エラーがある場合はtrue、ない場合はfalse
     */
    public function validationImage(array $profilePicture): bool
    {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxFileSize = 3 * 1024 * 1024;

        if ($profilePicture['error'] !== UPLOAD_ERR_OK) {
            $this->errorMessages[] = "プロフィール画像のアップロードに失敗しました。";
            return true;
        } elseif (strpos($profilePicture['name'], 'jpeg') === false) {
            $this->errorMessages[] = "プロフィール画像はJPEG、PNG形式のみ対応しています。";
            return true;
        } elseif (strpos($profilePicture['name'], 'png') === false) {
            $this->errorMessages[] = "プロフィール画像はJPEG、PNG形式のみ対応しています。";
            return true;
        }

        if (!\in_array($profilePicture['type'], $allowedTypes, true)) {
            $this->errorMessages[] = "プロフィール画像はJPEG、PNG形式のみ対応しています。";
            return true;
        }

        if ($profilePicture['size'] > $maxFileSize) {
            $this->errorMessages[] = "プロフィール画像のサイズは3MB以下にしてください。";
            return true;
        }

        return false;
    }

    /**
     * ユーザー名を検証する。
     */
    public function validationUserName(string $name): void
    {
        if (empty($name)) {
            $this->errorMessages[] = "名前を入力してください。";
        } elseif (preg_match('/[^\p{L}\p{N}]/u', $name)) {
            $this->errorMessages[] = "名前に記号を含めないでください。";
        }
    }

    /**
     * SNSのURLを検証する。
     */
    public function validationUrls(array $urls): void
    {
        // 少なくとも1つのURLが入力されているか確認
        if (empty($urls['X(Twitter)']) && empty($urls['YouTube']) && empty($urls['Twitch'])) {
            $this->errorMessages[] = "SNSリンクを1つ以上設定してください。";
        }

        // 各URLの形式を検証
        foreach ($urls as $platform => $url) {
            if (!empty($url)) {
                filter_var($url, FILTER_VALIDATE_URL) === false ||
                    !preg_match('/^https?:\/\//', $url) ? $this->errorMessages[] = "{$platform}のURLが正しくありません。" : "";
            }
        }

        // 空のエラーメッセージを削除
        $this->errorMessages = array_filter($this->errorMessages, fn($message) => !empty($message));

        // 各プラットフォームのURLに特有のドメインが含まれているか確認
        if (strpos($urls['X(Twitter)'], 'x.com') === false && !empty($urls['X(Twitter)'])) {
            $this->errorMessages[] = "X(Twitter)のURLが正しくありません。";
        } elseif (strpos($urls['YouTube'], 'youtube.com') === false && !empty($urls['YouTube'])) {
            $this->errorMessages[] = "YouTubeのURLが正しくありません。";
        } elseif (strpos($urls['Twitch'], 'twitch.tv') === false && !empty($urls['Twitch'])) {
            $this->errorMessages[] = "TwitchのURLが正しくありません。";
        }

        // 重複するエラーメッセージを削除
        $this->errorMessages = array_unique($this->errorMessages);
    }

    /**
     * 活動プラットフォームを検証する。
     */
    public function validationActivevity(bool $activityYoutube, bool $activityTwitch): void
    {
        if (!$activityYoutube && !$activityTwitch) {
            $this->errorMessages[] = "1つ以上の活動プラットフォームを選択してください。";
        }
    }

    /**
     * エラーメッセージを取得する。
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

}