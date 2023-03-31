<?php

namespace Webman\Socialite\Contracts;


const SOC_CLIENT_ID = 'client_id';
const SOC_CLIENT_SECRET = 'client_secret';
const SOC_RESPONSE_TYPE = 'response_type';
const SOC_SCOPE = 'scope';
const SOC_STATE = 'state';
const SOC_REDIRECT_URI = 'redirect_uri';
const SOC_ERROR = 'error';
const SOC_ERROR_DESCRIPTION = 'error_description';
const SOC_ERROR_URI = 'error_uri';
const SOC_GRANT_TYPE = 'grant_type';
const SOC_CODE = 'code';
const SOC_ACCESS_TOKEN = 'access_token';
const SOC_TOKEN_TYPE = 'token_type';
const SOC_EXPIRES_IN = 'expires_in';
const SOC_USERNAME = 'username';
const SOC_PASSWORD = 'password';
const SOC_REFRESH_TOKEN = 'refresh_token';
const SOC_AUTHORIZATION_CODE = 'authorization_code';
const SOC_CLIENT_CREDENTIALS = 'client_credentials';

interface Provider
{
    public function redirect(?string $redirectUrl = null): string;

    public function userFromCode(string $code): User;

    public function userFromToken(string $token): User;

    public function withRedirectUrl(string $redirectUrl): self;

    public function withState(string $state): self;

    /**
     * @param  string[]  $scopes
     */
    public function scopes(array $scopes): self;

    public function with(array $parameters): self;

    public function withScopeSeparator(string $scopeSeparator): self;

    public function getClientId(): ?string;

    public function getClientSecret(): ?string;
}