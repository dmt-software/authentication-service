<?php

namespace DMT\AuthenticationService\Handlers\Token;

use BackedEnum;
use DMT\AuthenticationService\Contracts\UserEntity;
use DMT\AuthenticationService\Contracts\UserTokenEntity;
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

class UserTokenAuthenticationHandler implements TokenAuthenticationHandlerInterface
{
    private EntityRepository $userTokenRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
        #[ConfigValue('authentication.token', 'DMT\Entity\UserToken')]
        private string $tokenEntity
    ) {
        if (!class_exists($tokenEntity) || !is_a($tokenEntity, UserTokenEntity::class, true)) {
            throw new InvalidArgumentException('Entity must implement UserTokenEntity');
        }

        $this->userTokenRepository = $entityManager->getRepository($this->tokenEntity);
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
        $userToken = $this->userTokenRepository->findOneBy($this->prepareParameters($parameters));

        if ($userToken === null || !$userToken->isValid() || !$userToken->user) {
            throw new AuthenticationException('Invalid token.');
        }

        return $userToken;
    }

    /**
     * @inheritDoc
     */
    public function generateToken(#[SensitiveParameter] array $parameters): UserTokenEntity
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
            /** @var UserTokenEntity $token */
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

        if (is_a(BackedEnum::class, $reasonPropertyType->getName(), true)) {
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
