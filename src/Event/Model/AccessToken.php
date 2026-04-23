<?php

namespace DMT\AuthenticationService\Event\Model;

use BackedEnum;
use SensitiveParameter;

class AccessToken
{
    public function __construct(
        #[SensitiveParameter]
        public string $token,
        public string|BackedEnum $reason,
    ) {
    }
}
