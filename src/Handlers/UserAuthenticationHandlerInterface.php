<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Handlers;

use DMT\AuthenticationService\Event\Model\AuthenticatedUser;
use DMT\AuthenticationService\Event\Model\UpdatedUser;
use DMT\AuthenticationService\Event\Model\UpdatePassword;
use DMT\AuthenticationService\Event\Model\UserCredentials;
use DMT\AuthenticationService\Exceptions\AuthenticationException;

interface UserAuthenticationHandlerInterface
{
    /**
     * @throws AuthenticationException
     */
    public function authenticate(UserCredentials $credentials): AuthenticatedUser;
}
