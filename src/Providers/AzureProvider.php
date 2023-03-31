<?php
/**
 * Azure Provider

 */

namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

class AzureProvider extends AbstractProvider
{
    public const NAME = 'azure';

    protected array $scopes = ['User.Read'];

    protected string $scopeSeparator = ' ';

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->getBaseUrl().'/oauth2/v2.0/authorize');
    }

    /**
     * @return string
     */
    protected function getBaseUrl(): string
    {
        return 'https://login.microsoftonline.com/'.$this->config['tenant'];
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return $this->getBaseUrl().'/oauth2/v2.0/token';
    }

    /**
     * @param string $token
     * @param array|null $query
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $response = $this->getHttpClient()->get(
            'https://graph.microsoft.com/v1.0/me',
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
            Contracts\SOC_ID => $user[Contracts\SOC_ID] ?? null,
            Contracts\SOC_NICKNAME => null,
            Contracts\SOC_NAME => $user['displayName'] ?? null,
            Contracts\SOC_EMAIL => $user['userPrincipalName'] ?? null,
            Contracts\SOC_AVATAR => null,
        ]);
    }

}