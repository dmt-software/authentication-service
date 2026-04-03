<?php

namespace DMT\AuthenticationService\Model;

use SensitiveParameter;

final class UserCredentials extends CredentialsObject
{
    public function __construct(
        public string $email,
        #[SensitiveParameter]
        public string $password,
    ) {
    }
}
