<?php
/**
 * Lark Provider
 */
namespace Webman\Socialite\Providers;

/**
 * @see https://open.larksuite.com/document/ukTMukTMukTM/uITNz4iM1MjLyUzM
 */
class LarkProvider extends FeiShuProvider
{
    public const NAME = 'lark';

    protected string $baseUrl = 'https://open.larksuite.com/open-apis';
}
