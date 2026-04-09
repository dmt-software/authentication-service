<?php

namespace DMT\AuthenticationService\Handlers;

use DMT\AuthenticationService\Contracts\UserTokenEntity;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use SensitiveParameter;

interface TokenAuthenticationHandlerInterface
{
    /**
     * @throws AuthenticationException
     */
    public function authenticate(#[SensitiveParameter] array $parameters): UserTokenEntity;
}
