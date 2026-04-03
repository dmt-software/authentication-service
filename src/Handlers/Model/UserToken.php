<?php

namespace DMT\AuthenticationService\Model;

use SensitiveParameter;

final class UserToken extends CredentialsObject
{
    public function __construct(
        #[SensitiveParameter]
        public string $token,
        public string $reason,
    ) {
    }
}
