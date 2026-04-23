<?php

namespace DMT\AuthenticationService\Event\Model;

use BackedEnum;
use DateTimeImmutable;
use DMT\AuthenticationService\Contracts\UserEntity;

class CreateToken
{
    public function __construct(
        #[SensitiveParameter]
        public string $token,
        public string|BackedEnum $reason,
        public UserEntity $user,
        public ?DateTimeImmutable $expiresAt = null,
    ) {
    }
}
