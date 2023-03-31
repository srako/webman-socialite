<?php
/**
 * Weibo Provider
 */
namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

/**
 * @see http://open.weibo.com/wiki/%E6%8E%88%E6%9D%83%E6%9C%BA%E5%88%B6%E8%AF%B4%E6%98%8E [OAuth 2.0 授权机制说明]
 */
class WeiboProvider extends AbstractProvider
{
    public const NAME = 'weibo';

    protected string $baseUrl = 'https://api.weibo.com';

    protected array $scopes = [Contracts\SOC_EMAIL];

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth2/authorize');
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/2/oauth2/access_token';
    }


    /**
     * @throws Exceptions\InvalidTokenException
     */
    protected function getUserByToken(string $token): array
    {
        $uid = $this->getTokenPayload($token)['uid'] ?? null;

        if (empty($uid)) {
            throw new Exceptions\InvalidTokenException('Invalid token.', $token);
        }

        $response = $this->getHttpClient()->get($this->baseUrl.'/2/users/show.json', [
            'query' => [
                'uid' => $uid,
                Contracts\SOC_ACCESS_TOKEN => $token,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return $this->fromJsonBody($response);
    }

    /**
     * @throws Exceptions\InvalidTokenException
     */
    protected function getTokenPayload(string $token): array
    {
        $response = $this->getHttpClient()->post($this->baseUrl.'/oauth2/get_token_info', [
            'query' => [
                Contracts\SOC_ACCESS_TOKEN => $token,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $response = $this->fromJsonBody($response);

        if (empty($response['uid'] ?? null)) {
            throw new Exceptions\InvalidTokenException(\sprintf('Invalid token %s', $token), $token);
        }

        return $response;
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SOC_ID => $user[Contracts\SOC_ID] ?? null,
            Contracts\SOC_NICKNAME => $user['screen_name'] ?? null,
            Contracts\SOC_NAME => $user[Contracts\SOC_NAME] ?? null,
            Contracts\SOC_EMAIL => $user[Contracts\SOC_EMAIL] ?? null,
            Contracts\SOC_AVATAR => $user['avatar_large'] ?? null,
        ]);
    }
}
