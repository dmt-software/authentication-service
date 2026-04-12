<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Handlers\PublicProperty;

use DMT\AuthenticationService\Contracts\UserEntity;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\UserAuthenticationHandlerInterface;
use DMT\AuthenticationService\Password\PasswordHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use SensitiveParameter;

final readonly class EmailPasswordAuthenticationHandler implements UserAuthenticationHandlerInterface
{
    private EntityRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        private PasswordHandlerInterface $passwordHandler,
        #[ConfigValue('authentication.user', 'DMT\Entity\User')]
        string $userEntity
    ) {
        if (!class_exists($userEntity) || !is_a($userEntity, UserEntity::class, true)) {
            throw new InvalidArgumentException('Entity must implement UserEntity');
        }

        $this->userRepository = $entityManager->getRepository($userEntity);
    }

    /**
     * Authenticate using email and password.
     *
     * {@inheritDoc}
     */
    public function authenticate(#[SensitiveParameter] array $parameters): UserEntity
    {
        if (!isset($parameters['email']) || !isset($parameters['password'])) {
            throw new AuthenticationException('Invalid credentials.');
        }

        /** @var UserEntity $user */
        $user = $this->userRepository->findOneBy(['email' => $parameters['email']]);

        if ($user === null || !$user->isActive()) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (!$this->passwordHandler->verify($parameters['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return $user;
    }
}
