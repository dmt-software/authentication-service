<?php

namespace DMT\AuthenticationService\Event\Model;

use DMT\AuthenticationService\Contracts\UserEntity;

class UpdatedUser
{
    public function __construct(
        public UserEntity $user
    ) {
    }
}
