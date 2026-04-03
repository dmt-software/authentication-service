<?php

declare(strict_types=1);

namespace DMT\AuthenticationService;

use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\AuthenticationHandlerInterface;

interface AuthenticationServiceInterface
{
    /**
     * @param class-string<AuthenticationHandlerInterface> $handlerClass
     * @throws AuthenticationException
     */
    public function authenticate(
        string $handlerClass,
        #[\SensitiveParameter] array $parameters = [],
        bool $persist = false
    ): object;

    /**
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getAuthenticatedUser(): ?object;
}
