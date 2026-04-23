<?php

namespace DMT\AuthenticationService\Handlers\Entity;

use DMT\AuthenticationService\Contracts\UserEntity;
use DMT\AuthenticationService\Event\Model\AuthenticatedUser;
use DMT\AuthenticationService\Event\Model\UpdatedUser;
use DMT\AuthenticationService\Event\Model\UpdatePassword;
use DMT\AuthenticationService\Event\Model\UserCredentials;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\UserAuthenticationHandlerInterface;
use DMT\AuthenticationService\Password\PasswordHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionProperty;

class EmailPasswordAuthenticationHandler implements UserAuthenticationHandlerInterface
{
    /**
     * @param class-string<UserEntity> $userEntity
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PasswordHandlerInterface $passwordHandler,
        #[ConfigValue('authentication.user', 'DMT\Entity\User')]
        private string $userEntity
    ) {
    }

    /**
     * Authenticate user by email and password.
     *
     * {@inheritDoc}
     */
    public function authenticate(UserCredentials $credentials): AuthenticatedUser
    {
        if (!isset($credentials->email) || empty($credentials->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        /** @var UserEntity $user */
        $user = $this->entityManager
            ->getRepository($this->userEntity)
            ->findOneBy([
                'email' => $credentials->email
            ]);

        if ($user === null || !$user->isActive()) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $password = new ReflectionProperty($user, 'password')->getValue($user);

        if (!$this->passwordHandler->verify($credentials->password, $password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return new AuthenticatedUser($user);
    }

    public function updatePassword(UpdatePassword $updatePassword): UpdatedUser
    {
        $password = $this->passwordHandler->hash($updatePassword->password);

        new ReflectionProperty($updatePassword->user, 'password')
            ->setValue($updatePassword->user, $password);

        return new UpdatedUser($updatePassword->user);
    }
}
