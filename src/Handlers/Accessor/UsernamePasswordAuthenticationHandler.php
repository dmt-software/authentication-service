<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Handlers\Accessor;

use DMT\AuthenticationService\Contracts\UserEntity;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\UserAuthenticationHandlerInterface;
use DMT\AuthenticationService\Password\PasswordHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * This handler expects the following method to be present:
 *
 *  setPassword(string $password)
 */
final readonly class UsernamePasswordAuthenticationHandler implements UserAuthenticationHandlerInterface
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
        if (!method_exists($userEntity, 'setPassword')) {
            throw new InvalidArgumentException('Entity must have a setPassword method');
        }

        $this->userRepository = $entityManager->getRepository($userEntity);
    }

    /**
     * Authenticate using username and password.
     *
     * {@inheritDoc}
     */
    public function authenticate(#[SensitiveParameter] array $parameters): UserEntity
    {
        if (!isset($parameters['username']) || !isset($parameters['password'])) {
            throw new AuthenticationException('Invalid credentials.');
        }

        /** @var UserEntity $user */
        $user = $this->userRepository->findOneBy([
            'username' => $parameters['username']
        ]);

        if ($user === null || !$user->isActive()) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (!$this->passwordHandler->verify($parameters['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return $user;
    }

    /**
     * @param object|UserEntity{setPassword: callable(string):void} $user
     */
    public function updatePassword(UserEntity $user, #[SensitiveParameter] string $password): void
    {
        $user->setPassword($password);
    }
}
