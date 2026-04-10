<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Handlers;

use DateTimeImmutable;
use DMT\AuthenticationService\Contracts\UserEntity;
use DMT\AuthenticationService\Contracts\TokenEntity;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use InvalidArgumentException;
use SensitiveParameter;

interface TokenAuthenticationHandlerInterface
{
    /**
     * @param array{token: string, reason: string} $parameters
     *
     * @throws AuthenticationException
     */
    public function authenticate(#[SensitiveParameter] array $parameters): TokenEntity;

    /**
     * @param array{user: UserEntity, token: string, reason: string, expiresAt: DateTimeImmutable} $parameters
     *
     * @throws InvalidArgumentException
     */
    public function generateToken(#[SensitiveParameter] array $parameters): TokenEntity;
}
