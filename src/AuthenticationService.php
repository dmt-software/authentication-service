<?php

declare(strict_types=1);

namespace DMT\AuthenticationService;

use DMT\AuthenticationService\Handlers\AuthenticationHandlerInterface;
use DMT\AuthenticationService\Session\SessionHandlerInterface;
use DMT\DependencyInjection\Traits\HasContainer;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @template T of object
 */
final class AuthenticationService implements AuthenticationServiceInterface
{
    use HasContainer;

    /** @var class-string<T> */
    private readonly string $entityName;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SessionHandlerInterface $sessionHandler,
        string $entityName,
    ) {
        $this->entityName = $entityName;
    }

    /**
     * {@inheritDoc}
     *
     * @return T
     * @throws \DMT\AuthenticationService\Exceptions\AuthenticationException
     * @throws \DMT\DependencyInjection\Exceptions\NotFoundException
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function authenticate(
        string $handlerClass,
        #[\SensitiveParameter] array $parameters = [],
        bool $persist = false
    ): object {
        /** @var AuthenticationHandlerInterface $handler */
        $handler = $this->getContainer()->get($handlerClass);

        $user = $handler->authenticate($handlerClass::createCredentials($parameters));

        if ($persist) {
            $this->sessionHandler->login($user->id);
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     *
     * @return T|null
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
