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
use DMT\DependencyInjection\Attributes\ConfigValue;
use DMT\DependencyInjection\Container;
use DMT\DependencyInjection\ServiceProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

readonly class AuthenticationServiceProvider implements ServiceProviderInterface
{
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
            value: fn (): TokenAuthenticationHandlerInterface
                => $container->get(UserTokenAuthenticationHandler::class)
        );

        $container->set(
            id: UserAuthenticationHandlerInterface::class,
            value: fn (): UserAuthenticationHandlerInterface
                => $container->get(EmailPasswordAuthenticationHandler::class)
        );

        $container->set(
            id: AuthenticationServiceInterface::class,
            value: fn (): AuthenticationServiceInterface
                => $container->get(AuthenticationService::class)
        );

        $container->set(
            id: AuthenticationMiddleware::class,
            value: fn (): AuthenticationMiddleware
                => $container->get(AuthenticationMiddleware::class)
        );
    }
}
