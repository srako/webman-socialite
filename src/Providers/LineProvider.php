<?php
/**
 * Line Provider
 */

namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

/**
 * @see https://developers.line.biz/en/docs/line-login/integrate-line-login/ [Integrating LINE Login with your web app]
 */
class LineProvider extends AbstractProvider
{
    public const NAME = 'line';

    protected string $baseUrl = 'https://api.line.me/oauth2/';

    protected string $version = 'v2.1';

    protected array $scopes = ['profile'];

    protected function getAuthUrl(): string
    {
        $this->state = $this->state ?: \md5(\uniqid(Contracts\SOC_STATE, true));

        return $this->buildAuthUrlFromBase('https://access.line.me/oauth2/'.$this->version.'/authorize');
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.$this->version.'/token';
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get(
            'https://api.line.me/v2/profile',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        return $this->fromJsonBody($response);
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SOC_ID => $user['userId'] ?? null,
            Contracts\SOC_NAME => $user['displayName'] ?? null,
            Contracts\SOC_NICKNAME => $user['displayName'] ?? null,
            Contracts\SOC_AVATAR => $user['pictureUrl'] ?? null,
            Contracts\SOC_EMAIL => null,
        ]);
    }
}
