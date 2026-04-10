<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Handlers;

use DMT\AuthenticationService\Contracts\UserEntity;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use SensitiveParameter;

interface UserAuthenticationHandlerInterface
{
    /**
     * @throws AuthenticationException
     */
    public function authenticate(#[SensitiveParameter] array $parameters): UserEntity;
}
