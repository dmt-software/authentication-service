<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Handlers;

use DMT\AuthenticationService\Event\Model\AccessToken;
use DMT\AuthenticationService\Event\Model\CreateToken;
use DMT\AuthenticationService\Event\Model\GeneratedToken;
use DMT\AuthenticationService\Event\Model\ValidatedToken;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use InvalidArgumentException;

interface TokenAuthenticationHandlerInterface
{
    /**
     * @throws AuthenticationException
     */
    public function authenticate(AccessToken $accessToken): ValidatedToken;

    /**
     * @throws InvalidArgumentException
     */
    public function generateToken(CreateToken $createToken): GeneratedToken;
}
