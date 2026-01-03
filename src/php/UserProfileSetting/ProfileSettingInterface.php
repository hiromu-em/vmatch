<?php
declare(strict_types=1);

namespace Vmatch\UserProfileSetting;

interface ProfileSettingInterface
{
    public function setName(string $name): void;

    public function setActivityPlaces(bool $youtube, bool $twitch): void;

    public function setSocialMediaUrls(array $snsUrls): void;

    public function getUserProfile(): array;
}