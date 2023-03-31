<?php
/**
 * Figma Provider
 */

namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

class GiteeProvider extends AbstractProvider
{
    public const NAME = 'gitee';

    protected array $scopes = ['user_info'];

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://gitee.com/oauth/authorize');
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return 'https://gitee.com/oauth/token';
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $userUrl = 'https://gitee.com/api/v5/user';
        $response = $this->getHttpClient()->get($userUrl, [
            'query' => [
                Contracts\SOC_ACCESS_TOKEN => $token,
            ],
        ]);

        return $this->fromJsonBody($response);
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SOC_ID => $user[Contracts\SOC_ID] ?? null,
            Contracts\SOC_NICKNAME => $user['login'] ?? null,
            Contracts\SOC_NAME => $user[Contracts\SOC_NAME] ?? null,
            Contracts\SOC_EMAIL => $user[Contracts\SOC_EMAIL] ?? null,
            Contracts\SOC_AVATAR => $user['avatar_url'] ?? null,
        ]);
    }

}
