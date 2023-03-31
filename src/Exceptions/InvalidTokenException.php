<?php
/**
 *
 */

namespace Webman\Socialite\Exceptions;

class InvalidTokenException extends Exception
{
    public string $token;

    public function __construct(string $message, string $token)
    {
        parent::__construct($message, -1);

        $this->token = $token;
    }
}
