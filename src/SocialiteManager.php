<?php
/**
 *

 */

namespace Webman\Socialite;

use Closure;
use Webman\Socialite\Contracts;

class SocialiteManager implements Contracts\Factory
{
    protected array $resolved = [];
    protected static array $customCreators = [];

    private Config $config;

    public function __construct(array $config = [])
    {
        if (empty($config)) {
            $config = \config('plugin.srako.socialite.app.driver');
        }
        $this->config = new Config($config);
    }

    public function config(Config $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get a driver instance.
     *
     * @param string $driver
     * @return mixed
     */
    public function driver($driver = null): Contracts\Provider
    {
        $driver = \strtolower($driver);

        if (!isset($this->resolved[$driver])) {
            $this->resolved[$driver] = $this->createDriverProvider($driver);
        }

        return $this->resolved[$driver];
    }

    /**
     * extend more driver
     * @param string $name
     * @param Closure $callback
     * @return $this
     */
    public function extend(string $name, Closure $callback): self
    {
        self::$customCreators[\strtolower($name)] = $callback;

        return $this;
    }

    /**
     * @return array
     */
    public function getResolvedProviders(): array
    {
        return $this->resolved;
    }

    /**
     * @param string $provider
     * @param array $config
     * @return Contracts\Provider
     */
    public function buildProvider(string $provider, array $config): Contracts\Provider
    {
        $instance = new $provider($config);
        if (!$instance instanceof Contracts\Provider) {
            throw  new Exceptions\InvalidArgumentException("The {$provider} must be instanceof ProviderInterface.");
        }
        return $instance;
    }

    /**
     * @param $driver
     * @return Contracts\Provider
     */
    public function createDriverProvider($driver): Contracts\Provider
    {
        $config = $this->config->get($driver, []);
        if (isset($config['provider'])) {
            $provider = $config['provider'];
        } else {
            // 增加默认provider
            $provider = 'Webman\Socialite\Providers\\' . $this->studly($driver) . 'Provider';
        }

        if (isset(self::$customCreators[$provider])) {
            return $this->callCustomCreator($provider, $config);
        }

        if (!$this->isValidProvider($provider)) {
            throw new Exceptions\InvalidArgumentException("Provider [{$driver}] not supported.");
        }

        return $this->buildProvider($provider, $config);
    }

    /**
     * @param string $provider
     * @return bool
     */
    private function isValidProvider(string $provider): bool
    {
        return \is_subclass_of($provider, Contracts\Provider::class);
    }

    /**
     * @param string $provider
     * @param array $config
     * @return mixed
     */
    private function callCustomCreator(string $provider, array $config): Contracts\Provider
    {
        return self::$customCreators[$provider]($config);
    }

    public function studly($value)
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $value));

        $studlyWords = array_map(fn($word) => ucfirst($word), $words);

        return implode($studlyWords);
    }
}