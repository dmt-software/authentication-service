<?php

declare(strict_types=1);

namespace DMT\AuthenticationService;

use DMT\AuthenticationService\Handlers\PublicProperty\EmailPasswordAuthenticationHandler;
use DMT\AuthenticationService\Handlers\PublicProperty\TokenAuthenticationHandler;
use DMT\AuthenticationService\Handlers\UserAuthenticationHandlerInterface;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\AuthenticationService\Mailer\HtmlMailManager;
use DMT\AuthenticationService\Mailer\MailManagerInterface;
use DMT\AuthenticationService\Password\NativePasswordHandler;
use DMT\AuthenticationService\Password\PasswordHandlerInterface;
use DMT\AuthenticationService\Session\DefaultSessionHandler;
use DMT\AuthenticationService\Session\SessionHandlerInterface;
use DMT\DependencyInjection\ConfigurationInterface;
use DMT\DependencyInjection\Container;
use DMT\DependencyInjection\Exceptions\NotFoundException;
use DMT\DependencyInjection\ServiceProviderInterface;
use DMT\MailService\Adapters\MailAdapterInterface;
use Twig\Environment;

readonly class AuthenticationServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        if (!$container->has(Environment::class)) {
            NotFoundException::throwException(Environment::class);
        }

        if (!$container->has(MailAdapterInterface::class)) {
            NotFoundException::throwException(MailAdapterInterface::class);
        }

        $container->set(
            id: MailManagerInterface::class,
            value: fn (): MailManagerInterface => $container->get(HtmlMailManager::class)
        );

        $container->set(
            id: SessionHandlerInterface::class,
            value: fn (): SessionHandlerInterface => new DefaultSessionHandler()
        );

        $container->set(
            id: PasswordHandlerInterface::class,
            value: fn (): PasswordHandlerInterface => new NativePasswordHandler()
        );

        $config = $container->get(ConfigurationInterface::class);

        $container->set(
            id: TokenAuthenticationHandlerInterface::class,
            value: fn (): TokenAuthenticationHandlerInterface
                => $container->get(
                    $config->get('authentication.tokenHandler', TokenAuthenticationHandler::class),
                )
        );

        $container->set(
            id: UserAuthenticationHandlerInterface::class,
            value: fn (): UserAuthenticationHandlerInterface
                => $container->get(
                    $config->get('authentication.userHandler', EmailPasswordAuthenticationHandler::class),
                )
        );
    }
}
