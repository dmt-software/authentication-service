<?php

declare(strict_types=1);

namespace DMT\AuthenticationService;

use DateTimeImmutable;
use DMT\AuthenticationService\Contracts\UserEntity;
use DMT\AuthenticationService\Contracts\TokenEntity;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\UserAuthenticationHandlerInterface;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\AuthenticationService\Mailer\MailManagerInterface;
use DMT\AuthenticationService\Password\PasswordHandlerInterface;
use DMT\AuthenticationService\Session\SessionHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SensitiveParameter;

readonly class AuthenticationService
{
    private EntityRepository $userRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private SessionHandlerInterface $sessionHandler,
        private PasswordHandlerInterface $passwordHandler,
        private UserAuthenticationHandlerInterface $userAuthenticationHandler,
        private TokenAuthenticationHandlerInterface $tokenAuthenticationHandler,
        private MailManagerInterface $mailManager,
        #[ConfigValue('authentication.user', 'DMT\Entity\User')]
        string $userEntity
    ) {
        $this->userRepository = $entityManager->getRepository($userEntity);
    }

    /**
     * @throws AuthenticationException
     */
    public function authenticate(#[SensitiveParameter] array $parameters, bool $persist = false): UserEntity
    {
        $user = $this->userAuthenticationHandler->authenticate($parameters);

        if ($persist) {
            $this->sessionHandler->login($user->id);
        }

        return $user;
    }

    /**
     * @throws AuthenticationException
     */
    public function authenticateByToken(#[SensitiveParameter] array $parameters, bool $persist = false): TokenEntity
    {
        $token = $this->tokenAuthenticationHandler->authenticate($parameters);

        if ($persist && property_exists($token, 'user')) {
            $this->sessionHandler->login($token->user->id);
        }

        return $token;
    }

    public function clear(): void
    {
        $this->sessionHandler->logout();
    }

    public function forgotPassword(string $email): void
    {
        /** @var UserEntity $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user || !$user->isActive()) {
            return;
        }

        $token = $this->tokenAuthenticationHandler->generateToken([
            'user' => $user,
            'token' => uniqid('d', true),
            'reason' => 'forgot-password',
            'expiresAt' => new DateTimeImmutable('+20 minutes'),
        ]);

        $this->mailManager->sendForgotPasswordLink($email, $token);
    }

    public function resetPassword(string $token, string $password): void
    {
        $parameters = [
            'token' => $token,
            'reason' => 'forgot-password',
        ];

        $this->entityManager->wrapInTransaction(function () use ($parameters, $password): void {
            $token = $this->tokenAuthenticationHandler->authenticate($parameters);
            $token->markUsed();

            $this->userAuthenticationHandler->updatePassword($token->user, $this->passwordHandler->hash($password));

            $this->entityManager->persist($token->user);
            $this->entityManager->persist($token);
        });
    }

    public function getAuthenticatedUser(): ?UserEntity
    {
        $userId = $this->sessionHandler->getAuthenticatedUserId();

        if ($userId === null) {
            return null;
        }

        /** @var UserEntity $user */
        $user = $this->userRepository->find($userId);

        return $user;
    }
}
