<?php

declare(strict_types=1);

namespace DMT\AuthenticationService;

use SensitiveParameter;

interface AuthenticationServiceInterface
{
    /**
     * @throws \DMT\AuthenticationService\Exceptions\AuthenticationException
     */
    public function authenticate(#[SensitiveParameter] array $parameters, bool $persist = false): object;

    /**
     * @throws \DMT\AuthenticationService\Exceptions\AuthenticationException
     */
    public function authenticateByToken(#[SensitiveParameter] array $parameters, bool $persist = false): object;

    /**
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getAuthenticatedUser(): ?object;
}
