<?php

declare(strict_types=1);

namespace DMT\AuthenticationService;

use DMT\AuthenticationService\Exceptions\AuthenticationException as AuthenticationException;
use DMT\AuthenticationService\Handlers\UserAuthenticationHandlerInterface;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\AuthenticationService\Session\SessionHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use SensitiveParameter;

/**
 * @template Entity of object
 */
final readonly class AuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SessionHandlerInterface $sessionHandler,
        private UserAuthenticationHandlerInterface $userAuthenticationHandler,
        private TokenAuthenticationHandlerInterface $tokenAuthenticationHandler,
        /** @var class-string<Entity> */
        #[ConfigValue('athentication.user', 'DMT\Entity\User')]
        private string $entityName
    ) {
    }

    /**
     * @return Entity
     * @throws AuthenticationException
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    public function authenticate(#[SensitiveParameter] array $parameters, bool $persist = false): object
    {
        $user = $this->userAuthenticationHandler->authenticate($parameters);

        if ($persist) {
            $this->sessionHandler->login($user->id);
        }

        return $user;
    }

    /**
     * @return Entity
     * @throws AuthenticationException
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    public function authenticateByToken(#[SensitiveParameter] array $parameters, bool $persist = false): object
    {
        $user = $this->tokenAuthenticationHandler->authenticate($parameters)->user;

        if ($persist) {
            $this->sessionHandler->login($user->id);
        }

        return $user;
    }

    /**
     * @return Entity|null
     * @throws \Doctrine\ORM\Exception\ORMException
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
