<?php
/**
 * FaceBook Provider

 */
namespace Webman\Socialite\Providers;

use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

/**
 * @see https://developers.facebook.com/docs/graph-api [Facebook - Graph API]
 */
class FacebookProvider extends AbstractProvider
{
    public const NAME = 'facebook';

    protected string $graphUrl = 'https://graph.facebook.com';

    protected string $version = 'v3.3';

    protected array $fields = ['first_name', 'last_name', 'email', 'gender', 'verified'];

    protected array $scopes = ['email'];

    protected bool $popup = false;

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://www.facebook.com/'.$this->version.'/dialog/oauth');
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return $this->graphUrl.'/oauth/access_token';
    }

    /**
     * @param string $code
     * @return array
     * @throws Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
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
        $appSecretProof = \hash_hmac('sha256', $token, $this->getConfig()->get(Contracts\SOC_CLIENT_SECRET));

        $response = $this->getHttpClient()->get($this->graphUrl.'/'.$this->version.'/me', [
            'query' => [
                Contracts\SOC_ACCESS_TOKEN => $token,
                'appsecret_proof' => $appSecretProof,
                'fields' => $this->formatScopes($this->fields, $this->scopeSeparator),
            ],
            'headers' => [
                'Accept' => 'application/json',
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
        $userId = $user[Contracts\SOC_ID] ?? null;
        $avatarUrl = $this->graphUrl.'/'.$this->version.'/'.$userId.'/picture';

        $firstName = $user['first_name'] ?? null;
        $lastName = $user['last_name'] ?? null;

        return new AbstractUser([
            Contracts\SOC_ID => $user[Contracts\SOC_ID] ?? null,
            Contracts\SOC_NICKNAME => null,
            Contracts\SOC_NAME => $firstName.' '.$lastName,
            Contracts\SOC_EMAIL => $user[Contracts\SOC_EMAIL] ?? null,
            Contracts\SOC_AVATAR => $userId ? $avatarUrl.'?type=normal' : null,
            'avatar_original' => $userId ? $avatarUrl.'?width=1920' : null,
        ]);
    }

    /**
     * @return array
     */
    protected function getCodeFields(): array
    {
        $fields = parent::getCodeFields();

        if ($this->popup) {
            $fields['display'] = 'popup';
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function fields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return $this
     */
    public function asPopup(): self
    {
        $this->popup = true;

        return $this;
    }
}
