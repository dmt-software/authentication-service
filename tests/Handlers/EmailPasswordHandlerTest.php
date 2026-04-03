<?php

namespace DMT\Test\AuthenticationService\Handlers;

use DMT\AuthenticationService\Exceptions\AuthenticationException;use DMT\AuthenticationService\Handlers\EmailPasswordHandler;
use DMT\AuthenticationService\Password\NativePasswordHandler;
use DMT\Test\AuthenticationService\Fixtures\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class EmailPasswordHandlerTest extends TestCase
{
    public function testAuthenticate(): void
    {
        $user = new User();
        $user->id = 1;
        $user->email = 'user@example.com';
        $user->password = password_hash('password', PASSWORD_DEFAULT);

        $credentials = EmailPasswordHandler::createCredentials([
            'email' => 'user@example.com',
            'password' => 'password'
        ]);

        $handler = new EmailPasswordHandler(
            $this->getEntityManagerForUser($user),
            new NativePasswordHandler(),
            User::class
        );

        $handler->authenticate($credentials);
    }

    public function testAuthenticateWithUnknownUser(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $credentials = EmailPasswordHandler::createCredentials([
            'email' => 'user@example.com',
            'password' => 'password'
        ]);

        $handler = new EmailPasswordHandler(
            $this->getEntityManagerForUser(null),
            new NativePasswordHandler(),
            User::class
        );

        $handler->authenticate($credentials);
    }

    public function testAuthenticateWithInvalidCredentials(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $user = new User();
        $user->id = 1;
        $user->email = 'user@example.com';
        $user->password = password_hash('other-password', PASSWORD_DEFAULT);

        $credentials = EmailPasswordHandler::createCredentials([
            'email' => 'user@example.com',
            'password' => 'password'
        ]);

        $handler = new EmailPasswordHandler(
            $this->getEntityManagerForUser($user),
            new NativePasswordHandler(),
            User::class
        );

        $handler->authenticate($credentials);
    }

    public function testAuthenticateWithInactiveUser(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $user = new User();
        $user->email = 'user@example.com';
        $user->password = password_hash('password', PASSWORD_DEFAULT);

        $credentials = EmailPasswordHandler::createCredentials([
            'email' => 'user@example.com',
            'password' => 'password'
        ]);

        $handler = new EmailPasswordHandler(
            $this->getEntityManagerForUser($user),
            new NativePasswordHandler(),
            User::class
        );

        $handler->authenticate($credentials);
    }

    private function getEntityManagerForUser(?User $user): EntityManagerInterface
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('find')
            ->with(User::class)
            ->willReturnCallback(fn() => $user);

        return $manager;
    }
}
