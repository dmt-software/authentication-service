<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Event\Model;

use DMT\AuthenticationService\Contracts\UserEntity;

class AuthenticatedUser
{
    public function __construct(
        public readonly UserEntity $user,
        public bool $persist = false,
    ) {
    }
}
