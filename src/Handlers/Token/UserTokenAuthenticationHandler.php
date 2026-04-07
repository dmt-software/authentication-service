<?php

namespace DMT\AuthenticationService\Handlers\Token;

use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * @template Entity of object
 */
class UserTokenAuthenticationHandler implements TokenAuthenticationHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        /** @var class-string<Entity> */
        #[ConfigValue('authentication.token', 'DMT\Entity\Token')]
        private string $tokenEntity
    ) {
        if (!class_exists($this->tokenEntity) || !method_exists($this->tokenEntity, 'isValid')) {
            throw new InvalidArgumentException('Entity must implement "isValid" method');
        }
    }

    /**
     * Authenticate using a user token.
     *
     * @return Entity
     *
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function authenticate(#[SensitiveParameter] array $parameters): object
    {
        if (!isset($parameters['token']) || !isset($parameters['reason'])) {
            throw new AuthenticationException('Invalid token.');
        }

        $userToken = $this->entityManager->find($this->tokenEntity, $parameters);

        if ($userToken === null || ! $userToken->isValid() || ! $userToken->user) {
            throw new AuthenticationException('Invalid token.');
        }

        return $userToken;
    }
}
