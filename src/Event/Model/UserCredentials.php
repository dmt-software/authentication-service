<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Event\Model;

use AllowDynamicProperties;
use SensitiveParameter;

#[AllowDynamicProperties]
class UserCredentials
{
    public function __construct(
        #[SensitiveParameter]
        public string $password,
        string ...$properties
    ) {
        foreach ($properties as $property => $value) {
            $this->{$property} = $value;
        }
    }
}
