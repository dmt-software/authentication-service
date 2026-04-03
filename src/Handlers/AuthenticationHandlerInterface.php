<?php

namespace DMT\AuthenticationService\Handlers;

use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\Model\CredentialsObject;
use SensitiveParameter;

interface AuthenticationHandlerInterface
{
    public static function createCredentials(#[SensitiveParameter] array $credentials): CredentialsObject;

    /**
     * @throws AuthenticationException
     */
    public function authenticate(CredentialsObject $credentials): object;
}
