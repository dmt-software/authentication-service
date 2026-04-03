<?php

namespace DMT\AuthenticationService\Handlers;

use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\Model\CredentialsObject;
use DMT\AuthenticationService\Handlers\Model\UserCredentials;
use DMT\AuthenticationService\Password\PasswordHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * @template T of object
 */
final readonly class EmailPasswordHandler implements AuthenticationHandlerInterface
{
    /** @var class-string<T> */
    private string $entityName;

    public static function createCredentials(#[SensitiveParameter] array $credentials): UserCredentials
    {
        return UserCredentials::create($credentials);
    }

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PasswordHandlerInterface $passwordHandler,
        string $entityName
    ) {
        if (! class_exists($entityName) || ! method_exists($entityName, 'isActive')) {
            throw new InvalidArgumentException('Invalid entity');
        }

        $this->entityName = $entityName;
    }

    /**
     * Authenticate using email and password.
     *
     * @return T
     *
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function authenticate(UserCredentials|CredentialsObject $credentials): object
    {
        if ($credentials->email === null || $credentials->password === null) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $user = $this->entityManager->find($this->entityName, ['email' => $credentials->email]);

        if ($user === null || !$user->isActive()) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (!$this->passwordHandler->verify($credentials->password, $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return $user;
    }
}
