<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Handlers\Accessor;

use DMT\AuthenticationService\Contracts\TokenEntity;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\PrepareParametersTrait;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use SensitiveParameter;

/**
 * This handler expects the following assessors to be present:
 *
 *  setToken(string $token)
 *  setReason(string $reason)
 *  setExpiresAt(DateTimeImmutable|DateTimeInterface|null $expiresAt)
 *  setUser(UserEntity $user)
 */
class TokenAuthenticationHandler implements TokenAuthenticationHandlerInterface
{
    use PrepareParametersTrait;

    private EntityRepository $tokenRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
        #[ConfigValue('authentication.token', 'DMT\Entity\UserToken')]
        private string $tokenEntity
    ) {
        if (!class_exists($tokenEntity) || !is_a($tokenEntity, TokenEntity::class, true)) {
            throw new InvalidArgumentException('Entity must implement TokenEntity');
        }

        $this->tokenRepository = $entityManager->getRepository($this->tokenEntity);
    }

    /**
     * Authenticate using a user token.
     *
     * {@inheritDoc}
     *
     * @throws ReflectionException
     */
    public function authenticate(#[SensitiveParameter] array $parameters): TokenEntity
    {
        if (!isset($parameters['token']) || !isset($parameters['reason'])) {
            throw new AuthenticationException('Invalid token.');
        }

        /** @var TokenEntity $token */
        $token = $this->tokenRepository->findOneBy(
            $this->prepareParameters($parameters, $this->tokenEntity)
        );

        if ($token === null || !$token->isValid()) {
            throw new AuthenticationException('Invalid token.');
        }

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function generateToken(#[SensitiveParameter] array $parameters): TokenEntity
    {
        if (
            !isset($parameters['token'])
            || !isset($parameters['reason'])
            || !isset($parameters['user'])
            || !isset($parameters['expiresAt'])
        ) {
            throw new InvalidArgumentException('Cannot generate token.');
        }

        try {
            /** @var TokenEntity $token */
            $token = new ReflectionClass($this->tokenEntity)->newInstance();

            foreach ($this->prepareParameters($parameters, $this->tokenEntity) as $property => $value) {
                $setter = 'set' . ucfirst($property);
                $token->$setter($value);
            }

            $this->entityManager->persist($token);
            $this->entityManager->flush();

            return $token;
        } catch (ReflectionException) {
            throw new InvalidArgumentException('Cannot generate token.');
        }
    }
}
