<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Contracts;

use BackedEnum;
use DateTimeImmutable;

/**
 * @property string $token
 * @property BackedEnum|string $reason
 * @property DateTimeImmutable|null $expiresAt
 * @property UserEntity $user
 */
interface UserTokenEntity
{
    public function isValid(): bool;

    public function markUsed(): void;
}
