<?php

namespace DMT\AuthenticationService\Model;

abstract class CredentialsObject
{
    public static function create(array $credentials): static
    {
        return new static(...$credentials);
    }

    public function __debugInfo(): ?array
    {
        return get_class_vars(static::class);
    }
}
