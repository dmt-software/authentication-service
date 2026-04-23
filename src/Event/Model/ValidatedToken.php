<?php

namespace DMT\AuthenticationService\Event\Model;

use DMT\AuthenticationService\Contracts\TokenEntity;
use DMT\AuthenticationService\Contracts\UserEntity;
use ReflectionProperty;

class ValidatedToken
{
    public UserEntity $user {
        get {
            return new ReflectionProperty($this->token, 'user')
                ->getValue($this->token);
        }
    }

    public function __construct(
        public readonly TokenEntity $token,
        public bool $persist = false,
    ) {
    }
}
