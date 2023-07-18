<?php
/**
 * WeChat Provider
 */
namespace Webman\Socialite\Providers;

use Psr\Http\Message\ResponseInterface;
use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

/**
 * @see http://mp.weixin.qq.com/wiki/9/01f711493b5a02f24b04365ac5d8fd95.html [WeChat - 公众平台OAuth文档]
 * @see https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316505&token=&lang=zh_CN
 *      [网站应用微信登录开发指南]
 */
class WechatProvider extends AbstractProvider
{
    public const NAME = 'wechat';

    protected string $baseUrl = 'https://api.weixin.qq.com/sns';

    protected array $scopes = ['snsapi_login'];

    protected bool $withCountryCode = false;

    protected ?array $component = null;

    protected ?string $openid = null;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        if ($this->getConfig()->has('component')) {
            $this->prepareForComponent((array) $this->getConfig()->get('component'));
        }
    }

    /**
     * @param string $openid
     * @return $this
     */
    public function withOpenid(string $openid): self
    {
        $this->openid = $openid;

        return $this;
    }

    /**
     * @return $this
     */
    public function withCountryCode(): self
    {
        $this->withCountryCode = true;

        return $this;
    }

    /**
     * @param string $code
     * @return array
     * @throws Exceptions\AuthorizeFailedException
     */
    public function tokenFromCode(string $code): array
    {
        $response = $this->getTokenFromCode($code);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }

    /**
     * @param  array<string,string>  $componentConfig  [Contracts\SOC_ID => xxx, Contracts\SOC_TOKEN => xxx]
     */
    public function withComponent(array $componentConfig): self
    {
        $this->prepareForComponent($componentConfig);

        return $this;
    }

    /**
     * @return array|null
     */
    public function getComponent(): ?array
    {
        return $this->component;
    }

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        $path = 'oauth2/authorize';

        if (\in_array('snsapi_login', $this->scopes)) {
            $path = 'qrconnect';
        }

        return $this->buildAuthUrlFromBase("https://open.weixin.qq.com/connect/{$path}");
    }

    /**
     * @param string $url
     * @return string
     */
    protected function buildAuthUrlFromBase(string $url): string
    {
        $query = \http_build_query($this->getCodeFields(), '', '&', $this->encodingType);

        return $url.'?'.$query.'#wechat_redirect';
    }

    /**
     * @return array
     */
    protected function getCodeFields(): array
    {
        if (! empty($this->component)) {
            $this->with(\array_merge($this->parameters, ['component_appid' => $this->component[Contracts\SOC_ID]]));
        }

        return \array_merge([
            'appid' => $this->getClientId(),
            Contracts\SOC_REDIRECT_URI => $this->redirectUrl,
            Contracts\SOC_RESPONSE_TYPE => Contracts\SOC_CODE,
            Contracts\SOC_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
            Contracts\SOC_STATE => $this->state ?: \md5(\uniqid(Contracts\SOC_STATE, true)),
            'connect_redirect' => 1,
        ], $this->parameters);
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return \sprintf($this->baseUrl.'/oauth2%s/access_token', empty($this->component) ? '' : '/component');
    }

    /**
     * @param string $code
     * @return Contracts\User
     * @throws Exceptions\AuthorizeFailedException
     */
    public function userFromCode(string $code): Contracts\User
    {
        if (\in_array('snsapi_base', $this->scopes)) {
            return $this->mapUserToObject($this->fromJsonBody($this->getTokenFromCode($code)));
        }

        $token = $this->tokenFromCode($code);

        $this->withOpenid($token['openid']);

        $user = $this->userFromToken($token[$this->accessTokenKey]);

        return $user->setRefreshToken($token[Contracts\SOC_REFRESH_TOKEN])
            ->setExpiresIn($token[Contracts\SOC_EXPIRES_IN])
            ->setTokenResponse($token);
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $language = $this->withCountryCode ? null : ($this->parameters['lang'] ?? 'zh_CN');

        $response = $this->getHttpClient()->get($this->baseUrl.'/userinfo', [
            'query' => \array_filter([
                Contracts\SOC_ACCESS_TOKEN => $token,
                'openid' => $this->openid,
                'lang' => $language,
            ]),
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
            Contracts\SOC_ID => $user['openid'] ?? null,
            Contracts\SOC_NAME => $user[Contracts\SOC_NICKNAME] ?? null,
            Contracts\SOC_NICKNAME => $user[Contracts\SOC_NICKNAME] ?? null,
            Contracts\SOC_AVATAR => $user['headimgurl'] ?? null,
            Contracts\SOC_EMAIL => null,
        ]);
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return empty($this->component) ? [
            'appid' => $this->getClientId(),
            'secret' => $this->getClientSecret(),
            Contracts\SOC_CODE => $code,
            Contracts\SOC_GRANT_TYPE => Contracts\SOC_AUTHORIZATION_CODE,
        ] : [
            'appid' => $this->getClientId(),
            'component_appid' => $this->component[Contracts\SOC_ID],
            'component_access_token' => $this->component[Contracts\SOC_TOKEN],
            Contracts\SOC_CODE => $code,
            Contracts\SOC_GRANT_TYPE => Contracts\SOC_AUTHORIZATION_CODE,
        ];
    }

    /**
     * @param string $code
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getTokenFromCode(string $code): ResponseInterface
    {
        return $this->getHttpClient()->get($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'query' => $this->getTokenFields($code),
        ]);
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function prepareForComponent(array $component): void
    {
        $config = [];
        foreach ($component as $key => $value) {
            if (\is_callable($value)) {
                $value = \call_user_func($value, $this);
            }

            switch ($key) {
                case Contracts\SOC_ID:
                case Contracts\SOC_APP_ID:
                case 'component_app_id':
                    $config[Contracts\SOC_ID] = $value;
                    break;
                case Contracts\SOC_TOKEN:
                case Contracts\SOC_ACCESS_TOKEN:
                case 'app_token':
                case 'component_access_token':
                    $config[Contracts\SOC_TOKEN] = $value;
                    break;
            }
        }

        if (2 !== \count($config)) {
            throw new Exceptions\InvalidArgumentException('Please check your config arguments were available.');
        }

        if (1 === \count($this->scopes) && \in_array('snsapi_login', $this->scopes)) {
            $this->scopes = ['snsapi_base'];
        }

        $this->component = $config;
    }
}
