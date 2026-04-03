<?php

namespace DMT\AuthenticationService;

use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Model\CredentialsObject;
use DMT\AuthenticationService\Model\UserToken;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * @template T of object
 */
class UserTokenHandler implements AuthenticationHandlerInterface, UserTokenHandlerInterface
{
    /** @var class-string<T> */
    private string $entityName;

    public static function createCredentials(#[SensitiveParameter] array $credentials): UserToken
    {
        return UserToken::create($credentials);
    }

    public function __construct(
        private EntityManagerInterface $entityManager,
        string $entityName
    ) {
        if (! class_exists($entityName) || ! method_exists($entityName, 'isValid')) {
            throw new InvalidArgumentException('Invalid entity');
        }

        $this->entityName = $entityName;
    }

    /**
     * Authenticate using a user token.
     *
     * @return T
     *
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function authenticate(UserToken|CredentialsObject $credentials): object
    {
        if ($credentials->token === null || $credentials->reason === null) {
            throw new AuthenticationException('Invalid token.');
        }

        $token = $this->entityManager->find($this->entityName, get_object_vars($credentials));

        if ($token === null || ! $token->isValid()) {
            throw new AuthenticationException('Invalid token.');
        }

        return $token->user;
    }
}
