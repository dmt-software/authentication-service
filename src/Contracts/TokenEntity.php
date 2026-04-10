<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Contracts;

use BackedEnum;
use DateTimeImmutable;

/**
 * @property BackedEnum|string $reason
 */
interface TokenEntity
{
    public string $token {
        get;
        set;
    }

    public DateTimeImmutable|null $expiresAt {
        get;
        set;
    }

    /**
     * Check if the token is valid.
     */
    public function isValid(): bool;

    /**
     * Mark the token used.
     */
    public function markUsed(): void;
}
