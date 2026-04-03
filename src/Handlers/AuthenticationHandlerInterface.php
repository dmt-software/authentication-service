<?php

namespace DMT\AuthenticationService;

use ArrayObject;
use DMT\AuthenticationService\Model\CredentialsObject;
use SensitiveParameter;

interface AuthenticationHandlerInterface
{
    public static function createCredentials(#[SensitiveParameter] array $credentials): CredentialsObject;

    /**
     * @throws \DMT\AuthenticationService\Exceptions\AuthenticationException
     */
    public function authenticate(CredentialsObject $credentials): object;
}
