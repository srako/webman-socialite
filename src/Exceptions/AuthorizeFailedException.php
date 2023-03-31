<?php
/**
 *
 */

namespace Webman\Socialite\Exceptions;



class AuthorizeFailedException extends Exception
{
    public array $body;


    public function __construct(string $message, $body)
    {
        parent::__construct($message, 400);

        $this->body = (array) $body;
    }
}
