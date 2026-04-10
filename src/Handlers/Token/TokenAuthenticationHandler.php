<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Handlers\Token;

use BackedEnum;
use DMT\AuthenticationService\Contracts\TokenEntity;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use SensitiveParameter;

class TokenAuthenticationHandler implements TokenAuthenticationHandlerInterface
{
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
     */
    public function authenticate(#[SensitiveParameter] array $parameters): TokenEntity
    {
        if (!isset($parameters['token']) || !isset($parameters['reason'])) {
            throw new AuthenticationException('Invalid token.');
        }

        /** @var TokenEntity $token */
        $token = $this->tokenRepository->findOneBy($this->prepareParameters($parameters));

        if ($token === null || !$token->isValid() || !$token->user) {
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

            foreach ($this->prepareParameters($parameters) as $property => $value) {
                $token->$property = $value;
            }

            $this->entityManager->persist($token);
            $this->entityManager->flush();

            return $token;
        } catch (ReflectionException) {
            throw new InvalidArgumentException('Cannot generate token.');
        }
    }

    private function prepareParameters(array $parameters): array
    {
        $reasonPropertyType = new ReflectionProperty($this->tokenEntity, 'reason')->getType();

        if (is_subclass_of($reasonPropertyType->getName(), BackedEnum::class)) {
            /** @var BackedEnum $enum */
            $enum = $reasonPropertyType->getName();

            $parameters['reason'] = $enum::tryFrom($parameters['reason']);
        } elseif ($reasonPropertyType->isBuiltin()) {
            settype($parameters['reason'], $reasonPropertyType->getName());
        } else {
            throw new ReflectionException('Invalid type for reason property');
        }

        return $parameters;
    }
}
