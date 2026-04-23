<?php

declare(strict_types=1);

namespace DMT\AuthenticationService;

use DMT\Apps\App;
use DMT\AuthenticationService\Event\AuthenticationServiceEventDispatcher;
use DMT\AuthenticationService\Event\Subscribers\PersistAuthenticatedUserEventSubscriber;
use DMT\AuthenticationService\Event\Subscribers\ReasonTypeEventSubscriber;
use DMT\AuthenticationService\Handlers\Entity\EmailPasswordAuthenticationHandler;
use DMT\AuthenticationService\Handlers\Entity\TokenReasonAuthenticationHandler;
use DMT\AuthenticationService\Handlers\UserAuthenticationHandlerInterface;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\AuthenticationService\Mailer\HtmlMailManager;
use DMT\AuthenticationService\Mailer\MailManagerInterface;
use DMT\AuthenticationService\Mailer\TextMailManager;
use DMT\AuthenticationService\Middlewares\AuthenticationMiddleware;
use DMT\AuthenticationService\Password\NativePasswordHandler;
use DMT\AuthenticationService\Password\PasswordHandlerInterface;
use DMT\AuthenticationService\Session\DefaultSessionHandler;
use DMT\AuthenticationService\Session\SessionHandlerInterface;
use DMT\DependencyInjection\Attributes\ConfigValue;
use DMT\DependencyInjection\Container;
use DMT\DependencyInjection\Exceptions\NotFoundException;
use DMT\DependencyInjection\ServiceProviderInterface;
use DMT\MailService\Adapters\MailAdapterInterface;

readonly class AuthenticationServiceProvider implements ServiceProviderInterface
{
    public function __construct(
        #[ConfigValue('authentication.userHandler', EmailPasswordAuthenticationHandler::class)]
        private string $userEntityClass = EmailPasswordAuthenticationHandler::class,
        #[ConfigValue('authentication.tokenHandler', TokenReasonAuthenticationHandler::class)]
        private string $tokenEntityClass = TokenReasonAuthenticationHandler::class,
        #[ConfigValue('authentication.mailManager', HtmlMailManager::class)]
        private string $mailManagerClass = TextMailManager::class,
        #[ConfigValue('authentication.sessionHandler', DefaultSessionHandler::class)]
        private string $sessionHandler = DefaultSessionHandler::class,
        #[ConfigValue('authentication.passwordHandler', NativePasswordHandler::class)]
        private string $passwordHandler = NativePasswordHandler::class,
    ) {
    }

    public function register(Container $container): void
    {
        if (!$container->has(MailAdapterInterface::class)) {
            NotFoundException::throwException(MailAdapterInterface::class);
        }

        $container->set(
            id: MailManagerInterface::class,
            value: fn (): MailManagerInterface => $container->get($this->mailManagerClass)
        );

        $container->set(
            id: SessionHandlerInterface::class,
            value: fn (): SessionHandlerInterface => $container->get($this->sessionHandler)
        );

        $container->set(
            id: PasswordHandlerInterface::class,
            value: fn (): PasswordHandlerInterface => $container->get($this->passwordHandler)
        );

        $container->set(
            id: TokenAuthenticationHandlerInterface::class,
            value: fn (): TokenAuthenticationHandlerInterface => $container->get($this->tokenEntityClass)
        );

        $container->set(
            id: UserAuthenticationHandlerInterface::class,
            value: fn (): UserAuthenticationHandlerInterface => $container->get($this->userEntityClass)
        );

        $container->set(
            id: AuthenticationServiceEventDispatcher::class,
            value: fn (): AuthenticationServiceEventDispatcher => new AuthenticationServiceEventDispatcher(
                $container->get(ReasonTypeEventSubscriber::class),
                $container->get(PersistAuthenticatedUserEventSubscriber::class),
            )
        );

        $container->set(
            id: AuthenticationService::class,
            value: fn (): AuthenticationService => $container->get(
                AuthenticationService::class,
                eventDispatcher: $container->get(AuthenticationServiceEventDispatcher::class)
            )
        );

        if ($container->has(App::class)) {
            $container->get(App::class)->addMiddleware(
                middleware: $container->get(AuthenticationMiddleware::class),
            );
        }
    }
}
