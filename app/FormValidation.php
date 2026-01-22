<?php
declare(strict_types=1);

namespace Vmatch;

use Vmatch\Result;

class FormValidation
{

    private array $arrayErrorMessage = [];

    private string $errorMessage = '';

    /**
     * プロフィール写真を検証する。
     * @param array $profilePicture アップロードされたプロフィール画像の情報
     * @return bool 検証結果
     */
    public function validateImage(array $profilePicture): bool
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxFileSize = 3 * 1024 * 1024;

        // アップロードエラーを確認
        if ($profilePicture['error'] !== UPLOAD_ERR_OK) {
            $this->arrayErrorMessage[] = "プロフィール画像のアップロードに失敗しました。";
            return true;
        }

        // ファイル名の拡張子を確認
        if (
            strpos($profilePicture['name'], 'jpeg') === false &&
            strpos($profilePicture['name'], 'png') === false &&
            strpos($profilePicture['name'], 'jpg') === false
        ) {
            $this->arrayErrorMessage[] = "プロフィール画像はJPEG、PNG、JPG形式のみ対応しています。";
            return true;
        }

        // MIMEタイプを確認
        if (!\in_array($profilePicture['type'], $allowedTypes, true)) {
            $this->arrayErrorMessage[] = "プロフィール画像はJPEG、PNG、JPG形式のみ対応しています。";
            return true;
        }

        if ($profilePicture['size'] > $maxFileSize) {
            $this->arrayErrorMessage[] = "プロフィール画像のサイズは3MB以下にしてください。";
            return true;
        }

        return false;
    }

    /**
     * ユーザー名を検証する。
     */
    public function validateUserName(string $name): void
    {
        if (empty($name)) {
            $this->arrayErrorMessage[] = "名前を入力してください。";
        } elseif (preg_match('/[^\p{L}\p{N}]/u', $name)) {
            $this->arrayErrorMessage[] = "名前に記号を含めないでください。";
        }
    }

    /**
     * SNSのURLを検証する。
     */
    public function validateUrls(array $urls): void
    {
        // 少なくとも1つのURLが入力されているか確認
        if (empty($urls['X(Twitter)']) && empty($urls['YouTube']) && empty($urls['Twitch'])) {
            $this->arrayErrorMessage[] = "SNSリンクを1つ以上設定してください。";
        }

        // 各URLの形式を検証
        foreach ($urls as $platform => $url) {
            if (!empty($url)) {
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    $this->arrayErrorMessage[] = "{$platform}のURLが正しくありません。";
                } elseif (!preg_match('/^https?:\/\//', $url)) {
                    $this->arrayErrorMessage[] = "{$platform}のURLが正しくありません。";
                }
            }
        }

        // 各プラットフォームのURLに特有のドメインが含まれているか確認
        if (strpos($urls['X(Twitter)'], 'x.com') === false && !empty($urls['X(Twitter)'])) {
            $this->arrayErrorMessage[] = "X(Twitter)のURLが正しくありません。";
        } elseif (strpos($urls['YouTube'], 'youtube.com') === false && !empty($urls['YouTube'])) {
            $this->arrayErrorMessage[] = "YouTubeのURLが正しくありません。";
        } elseif (strpos($urls['Twitch'], 'twitch.tv') === false && !empty($urls['Twitch'])) {
            $this->arrayErrorMessage[] = "TwitchのURLが正しくありません。";
        }

        // 重複するエラーメッセージを削除
        $this->arrayErrorMessage = array_unique($this->arrayErrorMessage);
    }

    /**
     * 活動プラットフォームを検証する。
     */
    public function validateActivity(bool $activityYoutube, bool $activityTwitch): void
    {
        if (!$activityYoutube && !$activityTwitch) {
            $this->arrayErrorMessage[] = "1つ以上の活動プラットフォームを選択してください。";
        }
    }

    /**
     * メールアドレス形式を検証する。
     * @param string|null $email
     * @return Result 検証結果
     */
    public function validateEmailFormat(?string $email): Result
    {
        if (empty($email)) {
            return Result::failure(['メールアドレスを入力してください。']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Result::failure(['メールアドレスの形式が間違っています。']);

        } elseif (!checkdnsrr(substr(strrchr($email, "@"), 1), "MX")) {
            return Result::failure(['メールアドレスの形式が間違っています。']);

        }

        return Result::success();
    }

    /**
     * パスワードの形式を検証する。 
     */
    public function validatePassword(?string $password): void
    {

        if (empty($password)) {
            $this->arrayErrorMessage[] = "パスワードを入力してください。";
            return;
        }

        if (mb_strlen($password) < 8) {
            $this->arrayErrorMessage[] = "パスワードは8文字以上で入力してください。";
        }

        if (!preg_match('/[A-Za-z]/', $password)) {
            $this->arrayErrorMessage[] = "英字を1文字含めてください。";
        }

        if (!preg_match('/\d/', $password)) {
            $this->arrayErrorMessage[] = "数字を1文字含めてください。";
        }

        if (!preg_match('/[@#\$%\^&\*]/', $password)) {
            $this->arrayErrorMessage[] = "記号(@ # $ % ^ & *) を1文字含めてください。";
        }
    }

    /**
     * エラーが含まれているか確認する。
     */
    public function hasErrorMessages(): bool
    {
        if (!empty($this->errorMessage) || !empty($this->arrayErrorMessage)) {
            return true;
        }
        return false;
    }

    /**
     * @return string エラーメッセージ
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @return array エラーメッセージ配列
     */
    public function getErrorMessages(): array
    {
        return $this->arrayErrorMessage;
    }

}