<?php

namespace DMT\AuthenticationService\Handlers;

use DMT\AuthenticationService\Exceptions\AuthenticationException;
use SensitiveParameter;

interface UserAuthenticationHandlerInterface
{
    /**
     * @throws AuthenticationException
     */
    public function authenticate(#[SensitiveParameter] array $parameters): object;
}
