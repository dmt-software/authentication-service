<?php

namespace DMT\AuthenticationService\Handlers\Token;

use DMT\AuthenticationService\Contracts\UserTokenEntity;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use SensitiveParameter;

class UserTokenAuthenticationHandler implements TokenAuthenticationHandlerInterface
{
    private EntityRepository $userTokenRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        #[ConfigValue('authentication.token', 'DMT\Entity\Token')]
        string $tokenEntity
    ) {
        if (!class_exists($tokenEntity) || !is_a($tokenEntity, UserTokenEntity::class, true)) {
            throw new InvalidArgumentException('Entity must implement UserTokenEntity');
        }

        $this->userTokenRepository = $entityManager->getRepository($tokenEntity);
    }

    /**
     * Authenticate using a user token.
     *
     * {@inheritDoc}
     */
    public function authenticate(#[SensitiveParameter] array $parameters): UserTokenEntity
    {
        if (!isset($parameters['token']) || !isset($parameters['reason'])) {
            throw new AuthenticationException('Invalid token.');
        }

        /** @var UserTokenEntity $userToken */
        $userToken = $this->userTokenRepository->findOneBy($parameters);

        if ($userToken === null || ! $userToken->isValid() || ! $userToken->user) {
            throw new AuthenticationException('Invalid token.');
        }

        return $userToken;
    }
}
