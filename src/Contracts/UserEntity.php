<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Contracts;

/**
 * @property string $email
 * @property string $password
 */
interface UserEntity
{
    public function isActive(): bool;
}
