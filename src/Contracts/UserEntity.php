<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Contracts;

interface UserEntity
{
    /**
     * Check if the user is active.
     */
    public function isActive(): bool;
}
