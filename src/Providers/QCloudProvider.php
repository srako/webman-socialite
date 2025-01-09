<?php
/**
 * QCloud Provider
 */
namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

class QCloudProvider extends AbstractProvider
{
    public const NAME = 'q-cloud';

    protected array $scopes = ['login'];

    protected string $accessTokenKey = 'UserAccessToken';

    protected string $refreshTokenKey = 'UserRefreshToken';

    protected string $expiresInKey = 'ExpiresAt';

    protected ?string $openId;

    protected ?string $unionId;

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://cloud.tencent.com/open/authorize');
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getAppId(): string
    {
        return $this->config->get(Contracts\SOC_APP_ID) ?? $this->getClientId();
    }

    /**
     * @return string
     */
    protected function getSecretId(): string
    {
        return $this->config->get('secret_id');
    }

    /**
     * @return string
     */
    protected function getSecretKey(): string
    {
        return $this->config->get('secret_key');
    }

    /**
     * @param string $code
     * @return array
     * @throws Exceptions\AuthorizeFailedException
     */
    public function TokenFromCode(string $code): array
    {
        $response = $this->performRequest(
            'GET',
            'open.tencentcloudapi.com',
            'GetUserAccessToken',
            '2018-12-25',
            [
                'query' => [
                    'UserAuthCode' => $code,
                ],
            ]
        );

        return $this->parseAccessToken($response);
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function getUserByToken(string $token): array
    {
        $secret = $this->getFederationToken($token);

        return $this->performRequest(
            'GET',
            'open.tencentcloudapi.com',
            'GetUserBaseInfo',
            '2018-12-25',
            [
                'headers' => [
                    'X-TC-Token' => $secret['Token'],
                ],
            ],
            $secret['TmpSecretId'],
            $secret['TmpSecretKey'],
        );
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        return new AbstractUser([
            Contracts\SOC_ID => $this->openId ?? null,
            Contracts\SOC_NAME => $user['Nickname'] ?? null,
            Contracts\SOC_NICKNAME => $user['Nickname'] ?? null,
        ]);
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    public function performRequest(string $method, string $host, string $action, string $version, array $options = [], ?string $secretId = null, ?string $secretKey = null): array
    {
        $method = \strtoupper($method);
        $timestamp = \time();
        $credential = \sprintf('%s/%s/tc3_request', \gmdate('Y-m-d', $timestamp), $this->getServiceFromHost($host));
        $options['headers'] = \array_merge(
            $options['headers'] ?? [],
            [
                'X-TC-Action' => $action,
                'X-TC-Timestamp' => $timestamp,
                'X-TC-Version' => $version,
                'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
            ]
        );

        $signature = $this->sign($method, $host, $options['query'] ?? [], '', $options['headers'], $credential, $secretKey);
        $options['headers']['Authorization'] =
            \sprintf(
                'TC3-HMAC-SHA256 Credential=%s/%s, SignedHeaders=content-type;host, Signature=%s',
                $secretId ?? $this->getSecretId(),
                $credential,
                $signature
            );
        $response = $this->getHttpClient()->get("https://{$host}/", $options);

        $response = $this->fromJsonBody($response);

        if (! empty($response['Response']['Error'])) {
            throw new Exceptions\AuthorizeFailedException(
                \sprintf('%s: %s', $response['Response']['Error']['Code'], $response['Response']['Error']['Message']),
                $response
            );
        }

        return $response['Response'] ?? [];
    }

    /**
     * @param string $requestMethod
     * @param string $host
     * @param array $query
     * @param string $payload
     * @param array $headers
     * @param string $credential
     * @param string|null $secretKey
     * @return bool|string
     */
    protected function sign(string $requestMethod, string $host, array $query, string $payload, array $headers, string $credential, ?string $secretKey = null): bool|string
    {
        $canonicalRequestString = \implode(
            "\n",
            [
                $requestMethod,
                '/',
                \http_build_query($query),
                "content-type:{$headers['Content-Type']}\nhost:{$host}\n",
                'content-type;host',
                \hash('SHA256', $payload),
            ]
        );

        $signString = \implode(
            "\n",
            [
                'TC3-HMAC-SHA256',
                $headers['X-TC-Timestamp'],
                $credential,
                \hash('SHA256', $canonicalRequestString),
            ]
        );

        $secretKey = $secretKey ?? $this->getSecretKey();
        $secretDate = \hash_hmac('SHA256', \gmdate('Y-m-d', $headers['X-TC-Timestamp']), "TC3{$secretKey}", true);
        $secretService = \hash_hmac('SHA256', $this->getServiceFromHost($host), $secretDate, true);
        $secretSigning = \hash_hmac('SHA256', 'tc3_request', $secretService, true);

        return \hash_hmac('SHA256', $signString, $secretSigning);
    }

    /**
     * @param array|string $body
     * @return array
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function parseAccessToken($body): array
    {
        if (! \is_array($body)) {
            $body = \json_decode($body, true);
        }

        if (empty($body['UserOpenId'] ?? null)) {
            throw new Exceptions\AuthorizeFailedException('Authorize Failed: '.\json_encode($body, JSON_UNESCAPED_UNICODE), $body);
        }

        $this->openId = $body['UserOpenId'] ?? null;
        $this->unionId = $body['UserUnionId'] ?? null;

        return $body;
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function getFederationToken(string $accessToken): array
    {
        $response = $this->performRequest(
            'GET',
            'sts.tencentcloudapi.com',
            'GetThirdPartyFederationToken',
            '2018-08-13',
            [
                'query' => [
                    'UserAccessToken' => $accessToken,
                    'Duration' => 7200,
                    'ApiAppId' => 0,
                ],
                'headers' => [
                    'X-TC-Region' => 'ap-guangzhou', // 官方人员说写死
                ],
            ]
        );

        if (empty($response['Credentials'] ?? null)) {
            throw new Exceptions\AuthorizeFailedException('Get Federation Token failed.', $response);
        }

        return $response['Credentials'];
    }

    /**
     * @return array
     */
    protected function getCodeFields(): array
    {
        $fields = \array_merge(
            [
                Contracts\SOC_APP_ID => $this->getAppId(),
                Contracts\SOC_REDIRECT_URI => $this->redirectUrl,
                Contracts\SOC_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
                Contracts\SOC_RESPONSE_TYPE => Contracts\SOC_CODE,
            ],
            $this->parameters
        );

        if ($this->state) {
            $fields[Contracts\SOC_STATE] = $this->state;
        }

        return $fields;
    }

    /**
     * @param string $host
     * @return string
     */
    protected function getServiceFromHost(string $host): string
    {
        return \explode('.', $host)[0] ?? '';
    }
}
