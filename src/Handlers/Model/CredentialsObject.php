<?php

namespace DMT\AuthenticationService\Handlers\Model;

abstract class CredentialsObject
{
    public static function create(array $credentials): static
    {
        return new static(...$credentials);
    }

    public function __debugInfo(): ?array
    {
        return array_keys(get_class_vars(static::class));
    }
}
