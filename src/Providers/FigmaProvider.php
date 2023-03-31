<?php
/**
 * Figma Provider
 */
namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

/**
 * @see https://www.figma.com/developers/api#oauth2
 */
class FigmaProvider extends AbstractProvider
{
    public const NAME = 'figma';

    protected string $scopeSeparator = '';

    protected array $scopes = ['file_read'];

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://www.figma.com/oauth');
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return 'https://www.figma.com/api/oauth/token';
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
            'form_params' => $this->getTokenFields($code),
        ]);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }


    /**
     * @return array
     */
    protected function getCodeFields(): array
    {
        return parent::getCodeFields() + [Contracts\SOC_STATE => \md5(\uniqid('state_', true))];
    }

    /**
     * @param string $token
     * @param array|null $query
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $response = $this->getHttpClient()->get('https://api.figma.com/v1/me', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
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
            Contracts\SOC_ID => $user[Contracts\SOC_ID] ?? null,
            'username' => $user[Contracts\SOC_EMAIL] ?? null,
            Contracts\SOC_NICKNAME => $user['handle'] ?? null,
            Contracts\SOC_NAME => $user['handle'] ?? null,
            Contracts\SOC_EMAIL => $user[Contracts\SOC_EMAIL] ?? null,
            Contracts\SOC_AVATAR => $user['img_url'] ?? null,
        ]);
    }
}
