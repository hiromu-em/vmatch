<?php
declare(strict_types=1);

namespace Vmatch\UserProfileSetting;

// use
use Vmatch\UserProfileSetting\ProfileSettingInterface;

require_once __DIR__ . '/../../../vendor/autoload.php';

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
     * 活動場所を設定する
     */
    public function setActivityPlaces(bool $youtube, bool $twitch): void
    {
        $this->userProfile['activityYoutube'] = $youtube;
        $this->userProfile['activityTwitch'] = $twitch;
    }

    /**
     * ソーシャルメディアのURLを設定する
     */
    public function setSocialMediaUrls(string ...$snsUrls): void
    {
        $this->userProfile['twitterUrl'] = $snsUrls[0];
        $this->userProfile['youtubeUrl'] = $snsUrls[1];
        $this->userProfile['twitchUrl'] = $snsUrls[2];
    }
}