<?php

namespace DMT\AuthenticationService\Model;

use ArrayObject;
use SensitiveParameter;
use SensitiveParameterValue;

final class Credentials extends ArrayObject
{
    /** @param array{email: string, password:string} $credentials */
    public function __construct(#[SensitiveParameter] array $credentials = [])
    {
        $credentials['password'] = new SensitiveParameterValue($credentials['password'] ?? null);

        parent::__construct(
            array_filter(
                $credentials,
                fn ($key) => $key == 'email' || $key == 'password',
                ARRAY_FILTER_USE_KEY
            )
        );
    }
}
