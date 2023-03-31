<?php
/**
 * Baidu Provider

 */

namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

/**
 * @see https://developer.baidu.com/wiki/index.php?title=docs/oauth [OAuth 2.0 授权机制说明]
 */
class BaiduProvider extends AbstractProvider
{
    public const NAME = 'baidu';

    protected string $baseUrl = 'https://openapi.baidu.com';

    protected string $version = '2.0';

    protected array $scopes = ['basic'];

    protected string $display = 'popup';

    /**
     * @param string $display
     * @return $this
     */
    public function withDisplay(string $display): self
    {
        $this->display = $display;

        return $this;
    }

    /**
     * @param array $scopes
     * @return $this
     */
    public function withScopes(array $scopes): self
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth/'.$this->version.'/authorize');
    }

    /**
     * @return array
     */
    protected function getCodeFields(): array
    {
        return [
            Contracts\SOC_RESPONSE_TYPE => Contracts\SOC_CODE,
            Contracts\SOC_CLIENT_ID => $this->getClientId(),
            Contracts\SOC_REDIRECT_URI => $this->redirectUrl,
            Contracts\SOC_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'display' => $this->display,
        ] + $this->parameters;
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/oauth/'.$this->version.'/token';
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get(
            $this->baseUrl.'/rest/'.$this->version.'/passport/users/getInfo',
            [
                'query' => [
                    Contracts\SOC_ACCESS_TOKEN => $token,
                ],
                'headers' => [
                    'Accept' => 'application/json',
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
            Contracts\SOC_ID => $user['userid'] ?? null,
            Contracts\SOC_NICKNAME => $user['realname'] ?? null,
            Contracts\SOC_NAME => $user['username'] ?? null,
            Contracts\SOC_EMAIL => '',
            Contracts\SOC_AVATAR => $user['portrait'] ? 'http://tb.himg.baidu.com/sys/portraitn/item/'.$user['portrait'] : null,
        ]);
    }
}
