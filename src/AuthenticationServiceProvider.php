<?php

declare(strict_types=1);

namespace DMT\AuthenticationService;

use DMT\AuthenticationService\Handlers\UserAuthenticationHandlerInterface;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\AuthenticationService\Handlers\User\EmailPasswordAuthenticationHandler;
use DMT\AuthenticationService\Handlers\Token\UserTokenAuthenticationHandler;
use DMT\AuthenticationService\Middlewares\AuthenticationMiddleware;
use DMT\AuthenticationService\Password\NativePasswordHandler;
use DMT\AuthenticationService\Password\PasswordHandlerInterface;
use DMT\AuthenticationService\Session\DefaultSessionHandler;
use DMT\AuthenticationService\Session\SessionHandlerInterface;
use DMT\DependencyInjection\Container;
use DMT\DependencyInjection\ServiceProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

readonly class AuthenticationServiceProvider implements ServiceProviderInterface
{
    public function __construct(
        private string $userEntity,
        private string $tokenEntity,
    ) {
    }

    public function register(Container $container): void
    {
        $container->set(
            id: SessionHandlerInterface::class,
            value: fn (): SessionHandlerInterface => new DefaultSessionHandler()
        );

        $container->set(
            id: PasswordHandlerInterface::class,
            value: fn (): PasswordHandlerInterface => new NativePasswordHandler()
        );

        $container->set(
            id: TokenAuthenticationHandlerInterface::class,
            value: fn (): TokenAuthenticationHandlerInterface => new UserTokenAuthenticationHandler(
                $container->get(EntityManagerInterface::class),
                $this->tokenEntity
            )
        );

        $container->set(
            id: UserAuthenticationHandlerInterface::class,
            value: fn (): UserAuthenticationHandlerInterface => new EmailPasswordAuthenticationHandler(
                $container->get(EntityManagerInterface::class),
                $container->get(PasswordHandlerInterface::class),
                $this->userEntity
            )
        );

        $container->set(
            id: AuthenticationServiceInterface::class,
            value: fn (): AuthenticationServiceInterface => new AuthenticationService(
                $container->get(EntityManagerInterface::class),
                $container->get(SessionHandlerInterface::class),
                $container->get(UserAuthenticationHandlerInterface::class),
                $container->get(TokenAuthenticationHandlerInterface::class),
                $this->userEntity
            )
        );

        $container->set(
            id: AuthenticationMiddleware::class,
            value: fn (): AuthenticationMiddleware => new AuthenticationMiddleware(
                $container->get(AuthenticationServiceInterface::class),
                $container->get(Environment::class),
            )
        );
    }
}
