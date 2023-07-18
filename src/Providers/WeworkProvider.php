<?php
/**
 * WeWork Provider
 */

namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

/**
 * @link https://developer.work.weixin.qq.com/document/path/91022
 */
class WeworkProvider extends AbstractProvider
{
    public const NAME = 'wework';

    protected bool $detailed = false;

    protected ?int $agentId = null;

    protected ?string $apiAccessToken;

    protected bool $asQrcode = false;

    protected string $baseUrl = 'https://qyapi.weixin.qq.com';

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        if ($this->getConfig()->has('base_url')) {
            $this->baseUrl = $this->getConfig()->get('base_url');
        }

        if ($this->getConfig()->has('agent_id')) {
            $this->agentId = $this->getConfig()->get('agent_id');
        }
    }

    /**
     * @return string|null
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $code
     * @return Contracts\User
     * @throws Exceptions\AuthorizeFailedException
     */
    public function userFromCode(string $code): Contracts\User
    {
        $token = $this->getApiAccessToken();
        $user = $this->getUser($token, $code);

        if ($this->detailed) {
            $user = $this->getUserById($user['UserId']);
        }

        return $this->mapUserToObject($user)->setProvider($this)->setRaw($user);
    }

    /**
     * @param int $agentId
     * @return $this
     */
    public function withAgentId(int $agentId): self
    {
        $this->agentId = $agentId;

        return $this;
    }

    /**
     * @return $this
     */
    public function detailed(): self
    {
        $this->detailed = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function asQrcode(): self
    {
        $this->asQrcode = true;

        return $this;
    }

    /**
     * @param string $apiAccessToken
     * @return $this
     */
    public function withApiAccessToken(string $apiAccessToken): self
    {
        $this->apiAccessToken = $apiAccessToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthUrl(): string
    {
        $scopes = $this->formatScopes($this->scopes, $this->scopeSeparator);
        $queries = array_filter([
            'appid' => $this->getClientId(),
            'agentid' => $this->agentId,
            Contracts\SOC_REDIRECT_URI => $this->redirectUrl,
            Contracts\SOC_RESPONSE_TYPE => Contracts\SOC_CODE,
            Contracts\SOC_SCOPE => $scopes,
            Contracts\SOC_STATE => $this->state,
        ]);

        if (! $this->agentId && (str_contains($scopes, 'snsapi_privateinfo') || $this->asQrcode)) {
            throw new Exceptions\InvalidArgumentException("agent_id is require when qrcode mode or scopes is 'snsapi_privateinfo'");
        }

        if ($this->asQrcode) {
            unset($queries[Contracts\SOC_SCOPE]);

            return \sprintf('https://open.work.weixin.qq.com/wwopen/sso/qrConnect?%s', http_build_query($queries));
        }

        return \sprintf('https://open.weixin.qq.com/connect/oauth2/authorize?%s#wechat_redirect', \http_build_query($queries));
    }

    /**
     * @throws Exceptions\MethodDoesNotSupportException
     */
    protected function getUserByToken(string $token): array
    {
        throw new Exceptions\MethodDoesNotSupportException('WeWork doesn\'t support access_token mode');
    }

    /**
     * @return string
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function getApiAccessToken(): string
    {
        return $this->apiAccessToken ?? $this->apiAccessToken = $this->requestApiAccessToken();
    }

    /**
     * @param string $token
     * @param string $code
     * @return array
     * @throws Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUser(string $token, string $code): array
    {
        $responseInstance = $this->getHttpClient()->get(
            $this->baseUrl.'/cgi-bin/user/getuserinfo',
            [
                'query' => \array_filter(
                    [
                        Contracts\SOC_ACCESS_TOKEN => $token,
                        Contracts\SOC_CODE => $code,
                    ]
                ),
            ]
        );

        $response = $this->fromJsonBody($responseInstance);

        if (($response['errcode'] ?? 1) > 0 || (empty($response['UserId']) && empty($response['OpenId']))) {
            throw new Exceptions\AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        } elseif (empty($response['UserId'])) {
            $this->detailed = false;
        }

        return $response;
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function getUserById(string $userId): array
    {
        $responseInstance = $this->getHttpClient()->post($this->baseUrl.'/cgi-bin/user/get', [
            'query' => [
                Contracts\SOC_ACCESS_TOKEN => $this->getApiAccessToken(),
                'userid' => $userId,
            ],
        ]);

        $response = $this->fromJsonBody($responseInstance);

        if (($response['errcode'] ?? 1) > 0 || empty($response['userid'])) {
            throw new Exceptions\AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        }

        return $response;
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser($this->detailed ? [
            Contracts\SOC_ID => $user['userid'] ?? null,
            Contracts\SOC_NAME => $user[Contracts\SOC_NAME] ?? null,
            Contracts\SOC_AVATAR => $user[Contracts\SOC_AVATAR] ?? null,
            Contracts\SOC_EMAIL => $user[Contracts\SOC_EMAIL] ?? null,
        ] : [
            Contracts\SOC_ID => $user['UserId'] ?? null ?: $user['OpenId'] ?? null,
        ]);
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function requestApiAccessToken(): string
    {
        $responseInstance = $this->getHttpClient()->get($this->baseUrl.'/cgi-bin/gettoken', [
            'query' => \array_filter(
                [
                    'corpid' => $this->config->get('corp_id')
                        ?? $this->config->get('corpid')
                        ?? $this->config->get(Contracts\SOC_CLIENT_ID),
                    'corpsecret' => $this->config->get('corp_secret')
                        ?? $this->config->get('corpsecret')
                        ?? $this->config->get(Contracts\SOC_CLIENT_SECRET),
                ]
            ),
        ]);

        $response = $this->fromJsonBody($responseInstance);

        if (($response['errcode'] ?? 1) > 0) {
            throw new Exceptions\AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        }

        return $response[Contracts\SOC_ACCESS_TOKEN];
    }

    protected function getTokenUrl(): string
    {
        return '';
    }
}
