<?php

declare(strict_types=1);

namespace DMT\AuthenticationService;

use DateTimeImmutable;
use DMT\AuthenticationService\Contracts\UserEntity;
use DMT\AuthenticationService\Contracts\UserTokenEntity;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\UserAuthenticationHandlerInterface;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\AuthenticationService\Mailer\MailManagerInterface;
use DMT\AuthenticationService\Session\SessionHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SensitiveParameter;

class AuthenticationService
{
    private EntityRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        private SessionHandlerInterface $sessionHandler,
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
    public function authenticateByToken(#[SensitiveParameter] array $parameters, bool $persist = false): UserEntity
    {
        $user = $this->tokenAuthenticationHandler->authenticate($parameters)->user;

        if ($persist) {
            $this->sessionHandler->login($user->id);
        }

        return $user;
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

        $parameters = [
            'user' => $user,
            'token' => uniqid('d', true),
            'reason' => 'forgot-password',
            'expiresAt' => new DateTimeImmutable('+20 minutes'),
        ];

        $this->mailManager->sendForgotPasswordLink(
            $this->tokenAuthenticationHandler->generateToken($parameters),
        );
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
