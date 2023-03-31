<?php

namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

/**
 * @see https://open.douyin.com/platform/resource/docs/openapi/account-permission/toutiao-get-permission-code
 */
class TouTiaoProvider extends DouYinProvider
{
    public const NAME = 'toutiao';

    protected string $baseUrl = 'https://open.snssdk.com';

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth/authorize/');
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SOC_ID => $user[Contracts\SOC_OPEN_ID] ?? null,
            Contracts\SOC_NAME => $user[Contracts\SOC_NICKNAME] ?? null,
            Contracts\SOC_NICKNAME => $user[Contracts\SOC_NICKNAME] ?? null,
            Contracts\SOC_AVATAR => $user[Contracts\SOC_AVATAR] ?? null,
        ]);
    }
}
