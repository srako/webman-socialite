<?php
/**
 * TaoBao Provider
 */
namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

/**
 * @see https://open.taobao.com/doc.htm?docId=102635&docType=1&source=search [Taobao - OAuth 2.0 授权登录]
 */
class TaobaoProvider extends AbstractProvider
{
    public const NAME = 'taobao';

    protected string $baseUrl = 'https://oauth.taobao.com';

    protected string $gatewayUrl = 'https://eco.taobao.com/router/rest';

    protected string $view = 'web';

    protected array $scopes = ['user_info'];

    /**
     * @param string $view
     * @return $this
     */
    public function withView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/authorize');
    }

    /**
     * @return array
     */
    public function getCodeFields(): array
    {
        return [
            Contracts\SOC_CLIENT_ID => $this->getClientId(),
            Contracts\SOC_REDIRECT_URI => $this->redirectUrl,
            'view' => $this->view,
            Contracts\SOC_RESPONSE_TYPE => Contracts\SOC_CODE,
        ];
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/token';
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return parent::getTokenFields($code) + [
            'view' => $this->view,
        ];
    }

    /**
     * @param string $code
     * @return array
     * @throws Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'query' => $this->getTokenFields($code),
        ]);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }

    /**
     * @param string $token
     * @param array|null $query
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $response = $this->getHttpClient()->post($this->getUserInfoUrl($this->gatewayUrl, $token));

        return $this->fromJsonBody($response);
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SOC_ID => $user[Contracts\SOC_OPEN_ID] ?? null,
            Contracts\SOC_NICKNAME => $user['nick'] ?? null,
            Contracts\SOC_NAME => $user['nick'] ?? null,
            Contracts\SOC_AVATAR => $user[Contracts\SOC_AVATAR] ?? null,
            Contracts\SOC_EMAIL => $user[Contracts\SOC_EMAIL] ?? null,
        ]);
    }

    /**
     * @param array $params
     * @return string
     */
    protected function generateSign(array $params): string
    {
        \ksort($params);

        $stringToBeSigned = $this->getConfig()->get(Contracts\SOC_CLIENT_SECRET);

        foreach ($params as $k => $v) {
            if (! \is_array($v) && ! \str_starts_with($v, '@')) {
                $stringToBeSigned .= "$k$v";
            }
        }

        $stringToBeSigned .= $this->getConfig()->get(Contracts\SOC_CLIENT_SECRET);

        return \strtoupper(\md5($stringToBeSigned));
    }

    /**
     * @param string $token
     * @param array $apiFields
     * @return array
     * @throws \Exception
     */
    protected function getPublicFields(string $token, array $apiFields = []): array
    {
        $fields = [
            'app_key' => $this->getClientId(),
            'sign_method' => 'md5',
            'session' => $token,
            'timestamp' => (new \DateTime('now', new \DateTimeZone('Asia/Shanghai')))->format('Y-m-d H:i:s'),
            'v' => '2.0',
            'format' => 'json',
        ];

        $fields = \array_merge($apiFields, $fields);
        $fields['sign'] = $this->generateSign($fields);

        return $fields;
    }

    /**
     * @param string $url
     * @param string $token
     * @return string
     * @throws \Exception
     */
    protected function getUserInfoUrl(string $url, string $token): string
    {
        $apiFields = ['method' => 'taobao.miniapp.userInfo.get'];

        $query = \http_build_query($this->getPublicFields($token, $apiFields), '', '&', $this->encodingType);

        return $url.'?'.$query;
    }
}
