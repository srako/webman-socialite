<?php
/**
 * DingTalk Provider

 */
namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

/**
 * @see http://open.douyin.com/platform
 * @see https://developer.open-douyin.com/docs/resource/zh-CN/dop/ability/user-management/get-user-info-solution
 */
class DouYinProvider extends AbstractProvider
{
    public const NAME = 'dou-yin';

    protected string $baseUrl = 'https://open.douyin.com';

    protected array $scopes = ['user_info'];

    protected ?string $openId;

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/platform/oauth/connect/');
    }

    /**
     * @return array
     */
    public function getCodeFields(): array
    {
        return [
            'client_key' => $this->getClientId(),
            Contracts\SOC_REDIRECT_URI => $this->redirectUrl,
            Contracts\SOC_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
            Contracts\SOC_RESPONSE_TYPE => Contracts\SOC_CODE,
        ];
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/oauth/access_token/';
    }

    /**
     * @param string $code
     * @return array
     * @throws Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->get(
            $this->getTokenUrl(),
            [
                'query' => $this->getTokenFields($code),
            ]
        );

        $body = $this->fromJsonBody($response);

        if (empty($body['data'] ?? null) || ($body['data']['error_code'] ?? -1) != 0) {
            throw new Exceptions\AuthorizeFailedException('Invalid token response', $body);
        }

        $this->withOpenId($body['data'][Contracts\SOC_OPEN_ID]);

        return $this->normalizeAccessTokenResponse($body['data']);
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return [
            'client_key' => $this->getClientId(),
            Contracts\SOC_CLIENT_SECRET => $this->getClientSecret(),
            Contracts\SOC_CODE => $code,
            Contracts\SOC_GRANT_TYPE => Contracts\SOC_AUTHORIZATION_CODE,
        ];
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $userUrl = $this->baseUrl.'/oauth/userinfo/';

        if (empty($this->openId)) {
            throw new Exceptions\InvalidArgumentException('please set the `open_id` before issue the API request.');
        }

        $response = $this->getHttpClient()->get(
            $userUrl,
            [
                'query' => [
                    Contracts\SOC_ACCESS_TOKEN => $token,
                    Contracts\SOC_OPEN_ID => $this->openId,
                ],
            ]
        );

        $body = $this->fromJsonBody($response);

        return $body['data'] ?? [];
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
            Contracts\SOC_EMAIL => $user[Contracts\SOC_EMAIL] ?? null,
        ]);
    }

    /**
     * @param string $openId
     * @return $this
     */
    public function withOpenId(string $openId): self
    {
        $this->openId = $openId;

        return $this;
    }
}
