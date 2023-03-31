<?php
/**
 *
 */

namespace Webman\Socialite\Facade;

use Closure;
use Webman\Socialite\Config;
use Webman\Socialite\Contracts\Provider;
use Webman\Socialite\SocialiteManager;

/**
 * @method static mixed driver($driver = null)
 * @method static mixed config(Config $config)
 * @method static mixed extend(string $name, Closure $callback)
 * @method static Provider with(array $parameters)
 * @method static Provider scopes(array $scopes)
 * @method static Provider redirect(?string $redirectUrl = null)
 * @method static Provider userFromCode(string $code)
 * @method static Provider userFromToken(string $token)
 * @method static Provider withState(string $state)
 * @method static Provider withScopeSeparator(string $scopeSeparator)
 * @method static Provider getClientId()
 * @method static Provider getClientSecret()
 * @method static Provider withRedirectUrl(string $redirectUrl)
 * @see SocialiteManager
 */
class Socialite
{
    protected static $_instance = null;

    public static function instance()
    {
        if (!static::$_instance) {
            static::$_instance = new SocialiteManager();
        }
        return static::$_instance;
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return static::instance()->{$method}(... $arguments);
    }
}