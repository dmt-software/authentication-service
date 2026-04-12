<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Contracts;

interface UserEntity
{
    public function isActive(): bool;
}
