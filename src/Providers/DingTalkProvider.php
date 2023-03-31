<?php
/**
 * DingTalk Provider
 */


namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

/**
 * “第三方个人应用”获取用户信息
 *
 * @see https://open.dingtalk.com/document/isvapp/obtain-identity-credentials
 *
 */
class DingTalkProvider extends AbstractProvider
{
    public const NAME = 'dingtalk';

    protected string $baseUrl = 'https://api.dingtalk.com/';

    protected string $apiVersion = 'v1.0';

    protected string $accessTokenKey = 'accessToken';

    protected string $refreshTokenKey = 'refreshToken';

    protected string $expiresInKey = 'expireIn';
    protected array $scopes = ['openid', 'corpid'];

    protected string $scopeSeparator = ' ';

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://login.dingtalk.com/oauth2/auth');
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function getTokenUrl(): string
    {
        return $this->baseUrl . $this->apiVersion . '/oauth2/userAccessToken';
    }

    protected function getCodeFields(): array
    {
        return [
                'prompt' => 'consent'
            ] + parent::getCodeFields();

    }

    protected function getTokenFields(string $code): array
    {
        return [
            'clientId' => $this->getClientId(),
            'clientSecret' => $this->getClientSecret(),
            Contracts\SOC_CODE => $code,
            Contracts\SOC_REDIRECT_URI => $this->redirectUrl,
            'grantType' => 'authorization_code'
        ];
    }

    public function tokenFromCode(string $code): array
    {
        try {
            $response = $this->getHttpClient()->post(
                $this->getTokenUrl(),
                [
                    'json' => $this->getTokenFields($code),
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]
            );
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                throw $e;
            }
        }

        return $this->normalizeAccessTokenResponse($response->getBody());
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get(
            $this->baseUrl . $this->apiVersion . '/contact/users/me',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'x-acs-dingtalk-access-token' => $token,
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
            Contracts\SOC_NAME => $user['nick'] ?? null,
            Contracts\SOC_NICKNAME => $user['nick'] ?? null,
            Contracts\SOC_ID => $user['unionId'],
            Contracts\SOC_EMAIL => $user['email'],
            Contracts\SOC_AVATAR => $user['avatarUrl'],
        ]);
    }

    protected function errorMessage(array $response): string
    {
        return $response['message'] ?? 'Unknown error';
    }

}
