<?php
declare(strict_types=1);

namespace Vmatch\UserProfileSetting;

class ProfileSetting implements ProfileSettingInterface
{
    private array $userProfile = [];

    /**
     * ユーザー名を設定する
     */
    public function setName(string $name): void
    {
        $this->userProfile['name'] = $name;
    }

    /**
     * 活動場所の媒体を設定する
     */
    public function setActivityPlaces(bool $youtube, bool $twitch): void
    {
        $this->userProfile['activityYoutube'] = $youtube;
        $this->userProfile['activityTwitch'] = $twitch;
    }

    /**
     * ソーシャルメディアのURLを設定する
     */
    public function setSocialMediaUrls(array $snsUrls): void
    {
        $this->userProfile['snsUrls'] = $snsUrls;
    }

    /**
     * ユーザーのプロフィールを取得する
     */
    public function getUserProfile(): array
    {
        return $this->userProfile;
    }
}