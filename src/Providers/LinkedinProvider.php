<?php

namespace Webman\Socialite\Providers;

use GuzzleHttp\Exception\GuzzleException;
use Webman\Socialite\AbstractUser;
use Webman\Socialite\Exceptions;
use Webman\Socialite\Contracts;

/**
 * @see https://developer.linkedin.com/docs/oauth2 [Authenticating with OAuth 2.0]
 */
class LinkedinProvider extends AbstractProvider
{
    public const NAME = 'linkedin';

    protected array $scopes = ['r_liteprofile', 'r_emailaddress'];

    /**
     * @return string
     */
    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://www.linkedin.com/oauth/v2/authorization');
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
    }

    /**
     * @param string $token
     * @param array|null $query
     * @return array
     * @throws GuzzleException
     */
    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $basicProfile = $this->getBasicProfile($token);
        $emailAddress = $this->getEmailAddress($token);

        return \array_merge($basicProfile, $emailAddress);
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getBasicProfile(string $token): array
    {
        $url = 'https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))';

        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
        ]);

        return $this->fromJsonBody($response);
    }

    /**
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getEmailAddress(string $token): array
    {
        $url = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';

        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
        ]);

        return $this->fromJsonBody($response)['elements.0.handle~'] ?? [];
    }

    /**
     * @param array $user
     * @return Contracts\User
     */
    protected function mapUserToObject(array $user): Contracts\User
    {
        $preferredLocale = ($user['firstName.preferredLocale.language'] ?? null).'_'.($user['firstName.preferredLocale.country'] ?? null);
        $firstName = $user['firstName.localized.'.$preferredLocale] ?? null;
        $lastName = $user['lastName.localized.'.$preferredLocale] ?? null;
        $name = $firstName.' '.$lastName;

        $images = $user['profilePicture.displayImage~.elements'] ?? [];
        $avatars = \array_filter($images, static fn ($image) => ($image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] ?? 0) === 100);
        $avatar = \array_shift($avatars);
        $originalAvatars = \array_filter($images, static fn ($image) => ($image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] ?? 0) === 800);
        $originalAvatar = \array_shift($originalAvatars);

        return new AbstractUser([
            Contracts\SOC_ID => $user[Contracts\SOC_ID] ?? null,
            Contracts\SOC_NICKNAME => $name,
            Contracts\SOC_NAME => $name,
            Contracts\SOC_EMAIL => $user['emailAddress'] ?? null,
            Contracts\SOC_AVATAR => $avatar['identifiers.0.identifier'] ?? null,
            'avatar_original' => $originalAvatar['identifiers.0.identifier'] ?? null,
        ]);
    }
}
