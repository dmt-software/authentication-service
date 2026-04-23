<?php

declare(strict_types=1);

namespace DMT\AuthenticationService;

use DateTimeImmutable;
use DMT\AuthenticationService\Contracts\UserEntity;
use DMT\AuthenticationService\Contracts\TokenEntity;
use DMT\AuthenticationService\Event\Model\AccessToken;
use DMT\AuthenticationService\Event\Model\CreateToken;
use DMT\AuthenticationService\Event\Model\UpdatePassword;
use DMT\AuthenticationService\Event\Model\UserCredentials;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\AuthenticationService\Handlers\UserAuthenticationHandlerInterface;
use DMT\AuthenticationService\Mailer\MailManagerInterface;
use DMT\AuthenticationService\Session\SessionHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use SensitiveParameter;

readonly class AuthenticationService
{
    private EntityRepository $userRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private SessionHandlerInterface $sessionHandler,
        private EventDispatcherInterface $eventDispatcher,
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
        $credentials = new UserCredentials(...$parameters);

        $this->eventDispatcher->dispatch($credentials);

        $authenticatedUser = $this->userAuthenticationHandler->authenticate($credentials);
        $authenticatedUser->persist = $persist;

        $this->eventDispatcher->dispatch($authenticatedUser);

        return $authenticatedUser->user;
    }

    /**
     * @throws AuthenticationException
     */
    public function authenticateByToken(#[SensitiveParameter] array $parameters, bool $persist = false): TokenEntity
    {
        $accessToken = new AccessToken(...$parameters);

        $this->eventDispatcher->dispatch($accessToken);

        $validatedToken = $this->tokenAuthenticationHandler->authenticate($accessToken);
        $validatedToken->persist = $persist;

        $this->eventDispatcher->dispatch($validatedToken);

        return $validatedToken->token;
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

        $createToken = new CreateToken(
            uniqid('d', true),
            'forgot-password',
            $user,
            new DateTimeImmutable('+20 minutes')
        );

        $this->eventDispatcher->dispatch($createToken);

        $generatedToken = $this->tokenAuthenticationHandler->generateToken($createToken);

        $this->eventDispatcher->dispatch($generatedToken);

        $this->mailManager->sendForgotPasswordLink($email, $generatedToken->token);
    }

    public function resetPassword(string $token, string $password): void
    {
        $accessToken = new AccessToken($token, 'forgot-password');

        $this->entityManager->wrapInTransaction(function () use ($accessToken, $password): void {
            $this->eventDispatcher->dispatch($accessToken);

            $validatedToken = $this->tokenAuthenticationHandler->authenticate($accessToken);

            $this->eventDispatcher->dispatch($validatedToken);

            $updatePassword = new UpdatePassword($validatedToken->user, $password);

            $this->eventDispatcher->dispatch($updatePassword);

            $changedUser = $this->userAuthenticationHandler->updatePassword($updatePassword);

            $this->eventDispatcher->dispatch($changedUser);

            $this->entityManager->persist($changedUser->user);
            $this->entityManager->persist($validatedToken->token);
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
