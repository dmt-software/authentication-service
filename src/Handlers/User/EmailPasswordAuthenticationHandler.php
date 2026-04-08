<?php

namespace DMT\AuthenticationService\Handlers\User;

use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\UserAuthenticationHandlerInterface;
use DMT\AuthenticationService\Password\PasswordHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * @template Entity of object
 */
final readonly class EmailPasswordAuthenticationHandler implements UserAuthenticationHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PasswordHandlerInterface $passwordHandler,
        /** @var class-string<Entity> */
        #[ConfigValue('authentication.user', 'DMT\Entity\User')]
        private string $userEntity
    ) {
        if (!class_exists($this->userEntity) || !method_exists($this->userEntity, 'isActive')) {
            throw new InvalidArgumentException('Entity must implement "isActive" method');
        }
    }

    /**
     * Authenticate using email and password.
     *
     * @return Entity
     *
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function authenticate(#[SensitiveParameter] array $parameters): object
    {
        if (!isset($parameters['email']) || !isset($parameters['password'])) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $user = $this->entityManager->getRepository($this->userEntity)->findOneBy(['email' => $parameters['email']]);

        if ($user === null || !$user->isActive()) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (!$this->passwordHandler->verify($parameters['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return $user;
    }
}
