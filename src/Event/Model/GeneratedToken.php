<?php

namespace DMT\AuthenticationService\Event\Model;

use DMT\AuthenticationService\Contracts\TokenEntity;

class GeneratedToken
{
    public function __construct(
        public TokenEntity $token
    ) {
    }
}
