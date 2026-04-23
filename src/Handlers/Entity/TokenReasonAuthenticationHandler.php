<?php

namespace DMT\AuthenticationService\Handlers\Entity;

use DMT\AuthenticationService\Contracts\TokenEntity;
use DMT\AuthenticationService\Event\Model\AccessToken;
use DMT\AuthenticationService\Event\Model\CreateToken;
use DMT\AuthenticationService\Event\Model\GeneratedToken;
use DMT\AuthenticationService\Event\Model\ValidatedToken;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;

final readonly class TokenReasonAuthenticationHandler implements TokenAuthenticationHandlerInterface
{
    /**
     * @param class-string<TokenEntity> $tokenEntity
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[ConfigValue('authentication.token', 'DMT\Entity\UserToken')]
        private string $tokenEntity
    ) {
    }

    /**
     * Authenticate using a user token.
     *
     * {@inheritDoc}
     */
    public function authenticate(AccessToken $accessToken): ValidatedToken
    {
        if (empty($accessToken->token) || empty($accessToken->reason)) {
            throw new AuthenticationException('Invalid token.');
        }

        /** @var TokenEntity $token */
        $token = $this->entityManager
            ->getRepository($this->tokenEntity)
            ->findOneBy([
                'token' => $accessToken->token,
                'reason' => $accessToken->reason,
            ]);

        if ($token === null || !$token->isValid()) {
            throw new AuthenticationException('Invalid token.');
        }

        $token->markUsed();

        return new ValidatedToken($token);
    }

    /**
     * Generate a token.
     *
     * {@inheritDoc}
     */
    public function generateToken(CreateToken $createToken): GeneratedToken
    {
        $tokenClass = new ReflectionClass($this->tokenEntity);
        /** @var TokenEntity $token */
        $token = $tokenClass->newInstance();

        $tokenClass->getProperty('token')->setValue($token, $createToken->token);
        $tokenClass->getProperty('reason')->setValue($token, $createToken->reason);
        $tokenClass->getProperty('expiresAt')->setValue($token, $createToken->expiresAt);
        $tokenClass->getProperty('user')->setValue($token, $createToken->user);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return new GeneratedToken($token);
    }
}
