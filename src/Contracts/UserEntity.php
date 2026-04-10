<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Contracts;

interface UserEntity
{
    public string $email {
        get;
        set;
    }

    public ?string $password {
        get;
        set;
    }

    public function isActive(): bool;
}
