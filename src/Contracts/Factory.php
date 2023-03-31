<?php
/**
 *
 */

namespace Webman\Socialite\Contracts;

use Webman\Socialite\Config;

const SOC_APP_ID = 'app_id';
const SOC_APP_SECRET = 'app_secret';
const SOC_OPEN_ID = 'open_id';
const SOC_TOKEN = 'token';
interface Factory
{

    /**
     * Get an OAuth provider implementation.
     *
     * @param  string  $driver
     */
    public function driver($driver = null);

    public function config(Config $config):self;

    public function getResolvedProviders(): array;

    public function buildProvider(string $provider, array $config): Provider;
}