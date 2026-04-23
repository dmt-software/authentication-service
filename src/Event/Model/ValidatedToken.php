<?php

namespace DMT\AuthenticationService\Event\Model;

use DMT\AuthenticationService\Contracts\TokenEntity;
use DMT\AuthenticationService\Contracts\UserEntity;
use ReflectionProperty;

class ValidatedToken
{
    // phpcs:disable
    public UserEntity $user {
        get => new ReflectionProperty($this->token, 'user')->getValue($this->token);
    }
    // phpcs:enable

    public function __construct(
        public readonly TokenEntity $token,
        public bool $persist = false,
    ) {
    }
}
