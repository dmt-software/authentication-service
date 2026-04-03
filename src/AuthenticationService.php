<?php

declare(strict_types=1);

namespace DMT\AuthenticationService;

use DMT\AuthenticationService\Session\SessionHandlerInterface;
use DMT\DependencyInjection\Traits\HasContainer;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @template T of object
 */
readonly class AuthenticationService
{
    use HasContainer;

    /** @var class-string<T> */
    private string $entityName;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private SessionHandlerInterface $sessionHandler,
        string $entityName,
    ) {
        $this->entityName = $entityName;
    }

    /**
     * @param class-string<\DMT\AuthenticationService\AuthenticationHandlerInterface> $handler
     *
     * @return T
     * @throws \DMT\AuthenticationService\Exceptions\AuthenticationException
     */
    public function authenticate(
        string $handler,
        #[\SensitiveParameter] array $parameters = [],
        bool $persist = false
    ): object {
        $user = $this->getContainer()->get($handler)->authenticate($handler::getCredentials($parameters));

        if ($persist) {
            $this->sessionHandler->login($user->id);
        }

        return $user;
    }

    /**
     * Get the user from session.
     *
     * @return T|null
     *
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getAuthenticatedUser(): ?object
    {
        $userId = $this->sessionHandler->getAuthenticatedUserId();

        if ($userId === null) {
            return null;
        }

        return $this->entityManager->find($this->entityName, $userId);
    }
}
