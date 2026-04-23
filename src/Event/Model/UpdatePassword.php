<?php

namespace DMT\AuthenticationService\Event\Model;

use DMT\AuthenticationService\Contracts\UserEntity;
use SensitiveParameter;

class UpdatePassword
{
    public function __construct(
        public UserEntity $user,
        #[SensitiveParameter]
        public string $password,
    ) {
    }
}
