<?php

declare(strict_types=1);

namespace DMT\Test\AuthenticationService;

use DMT\AuthenticationService\AuthenticationService;
use DMT\AuthenticationService\AuthenticationServiceProvider;
use DMT\AuthenticationService\Handlers\TokenAuthenticationHandlerInterface;
use DMT\AuthenticationService\Middlewares\AuthenticationMiddleware;
use DMT\AuthenticationService\Password\PasswordHandlerInterface;
use DMT\AuthenticationService\Session\SessionHandlerInterface;
use DMT\DependencyInjection\ConfigurationInterface;
use DMT\DependencyInjection\ContainerFactory;
use DMT\MailService\Adapters\MailAdapterInterface;
use DMT\Test\AuthenticationService\Fixtures\User;
use DMT\Test\AuthenticationService\Fixtures\Token;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class AuthenticationServiceProviderTest extends TestCase
{
    public function testRegister(): void
    {
        $config = $this->createMock(ConfigurationInterface::class);
        $config
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(function (string $key, mixed $default) {
                return match ($key) {
                    'authentication.user' => User::class,
                    'authentication.token' => Token::class,
                    'mailer.sender' => 'user@example.com',
                    'app.url' => 'http://example.com',
                    default => $default,
                };
            });

        $container = new ContainerFactory()->createContainer();
        $container->set(EntityManagerInterface::class, fn () => $this->createMock(EntityManagerInterface::class));
        $container->set(Environment::class, fn () => $this->createMock(Environment::class));
        $container->set(ConfigurationInterface::class, fn () => $config);
        $container->set(MailAdapterInterface::class, fn () => $this->createMock(MailAdapterInterface::class));
        $container->register(new AuthenticationServiceProvider());

        $this->assertInstanceOf(
            AuthenticationService::class,
            $container->get(AuthenticationService::class)
        );

        $this->assertInstanceOf(
            AuthenticationMiddleware::class,
            $container->get(AuthenticationMiddleware::class)
        );

        $this->assertInstanceOf(
            TokenAuthenticationHandlerInterface::class,
            $container->get(TokenAuthenticationHandlerInterface::class)
        );

        $this->assertInstanceOf(
            PasswordHandlerInterface::class,
            $container->get(PasswordHandlerInterface::class)
        );

        $this->assertInstanceOf(
            SessionHandlerInterface::class,
            $container->get(SessionHandlerInterface::class)
        );
    }
}
