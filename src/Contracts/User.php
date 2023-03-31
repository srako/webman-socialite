<?php
/**
 *
 */

namespace Webman\Socialite\Contracts;
const SOC_ID = 'id';
const SOC_NAME = 'name';
const SOC_NICKNAME = 'nickname';
const SOC_EMAIL = 'email';
const SOC_AVATAR = 'avatar';

interface User
{


    public function getId();

    public function getNickname(): ?string;

    public function getName(): ?string;

    public function getEmail(): ?string;

    public function getAvatar(): ?string;

    public function getAccessToken(): ?string;

    public function getRefreshToken(): ?string;

    public function getExpiresIn(): ?int;

    public function getProvider(): Provider;

    public function setRefreshToken(?string $refreshToken): self;

    public function setExpiresIn(int $expiresIn): self;

    public function setTokenResponse(array $response): self;

    public function getTokenResponse();

    public function setProvider(Provider $provider): self;

    public function getRaw(): array;

    public function setRaw(array $user): self;

    public function setAccessToken(string $token): self;
}