<?php
/**
 * coding provider

 */

namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

class CodingProvider extends AbstractProvider
{
    public const NAME = 'coding';

    protected string $teamUrl = ''; //https://{your-team}.coding.net

    protected array $scopes = ['user', 'user:email'];

    protected string $scopeSeparator = ',';

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $teamUrl = $this->config->get('team_url'); // https://{your-team}.coding.net

        if (! $teamUrl) {
            throw new Exceptions\InvalidArgumentException('Missing required config [team_url]');
        }

        // validate team_url
        if (filter_var($teamUrl, FILTER_VALIDATE_URL) === false) {
            throw new Exceptions\InvalidArgumentException('Invalid team_url');
        }
        $this->teamUrl = rtrim($teamUrl, '/');
    }

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase("$this->teamUrl/oauth_authorize.html");
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return "$this->teamUrl/api/oauth/access_token";
    }

    /**
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return [
            Contracts\SOC_CLIENT_ID => $this->getClientId(),
            Contracts\SOC_CLIENT_SECRET => $this->getClientSecret(),
            Contracts\SOC_CODE => $code,
            Contracts\SOC_GRANT_TYPE => Contracts\SOC_AUTHORIZATION_CODE,
        ];
    }

    /**
     * @param string $token
     * @return array
     * @throws Exceptions\BadRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $responseInstance = $this->getHttpClient()->get(
            "$this->teamUrl/api/me",
            [
                'query' => [
                    'access_token' => $token,
                ],
            ]
        );

        $response = $this->fromJsonBody($responseInstance);

        if (empty($response[Contracts\SOC_ID])) {
            throw new Exceptions\BadRequestException((string) $responseInstance->getBody());
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
            Contracts\SOC_NICKNAME => $user[Contracts\SOC_NAME] ?? null,
            Contracts\SOC_NAME => $user[Contracts\SOC_NAME] ?? null,
            Contracts\SOC_EMAIL => $user[Contracts\SOC_EMAIL] ?? null,
            Contracts\SOC_AVATAR => $user[Contracts\SOC_AVATAR] ?? null,
        ]);
    }
}