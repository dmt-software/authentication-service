<?php

namespace DMT\AuthenticationService\Handlers\Model;

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
