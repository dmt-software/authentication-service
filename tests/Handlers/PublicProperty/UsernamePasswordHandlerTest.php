<?php

declare(strict_types=1);

namespace DMT\Test\AuthenticationService\Handlers\PublicProperty;

use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\PublicProperty\UsernamePasswordAuthenticationHandler;
use DMT\AuthenticationService\Password\NativePasswordHandler;
use DMT\Test\AuthenticationService\Fixtures\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class UsernamePasswordHandlerTest extends TestCase
{
    public function testAuthenticate(): void
    {
        $user = new User();
        $user->id = 1;
        $user->username = 'admin';
        $user->password = password_hash('password', PASSWORD_DEFAULT);

        $handler = new UsernamePasswordAuthenticationHandler(
            $this->getEntityManagerForUser($user),
            new NativePasswordHandler(),
            User::class
        );

        $handler->authenticate(['username' => 'admin', 'password' => 'password']);
    }

    public function testAuthenticateWithUnknownUser(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $handler = new UsernamePasswordAuthenticationHandler(
            $this->getEntityManagerForUser(null),
            new NativePasswordHandler(),
            User::class
        );

        $handler->authenticate(['username' => 'admin', 'password' => 'password']);
    }

    public function testAuthenticateWithInvalidCredentials(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $user = new User();
        $user->id = 1;
        $user->email = 'user@example.com';
        $user->username = 'admin';
        $user->password = password_hash('other-password', PASSWORD_DEFAULT);

        $handler = new UsernamePasswordAuthenticationHandler(
            $this->getEntityManagerForUser($user),
            new NativePasswordHandler(),
            User::class
        );

        $handler->authenticate(['username' => 'admin', 'password' => 'password']);
    }

    public function testAuthenticateWithInactiveUser(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $user = new User();
        $user->username = 'admin';
        $user->password = password_hash('password', PASSWORD_DEFAULT);

        $handler = new UsernamePasswordAuthenticationHandler(
            $this->getEntityManagerForUser($user),
            new NativePasswordHandler(),
            User::class
        );

        $handler->authenticate(['username' => 'admin', 'password' => 'password']);
    }

    private function getEntityManagerForUser(?User $user): EntityManagerInterface
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturnCallback(
                function () use ($user) {
                    $repository = $this->createMock(EntityRepository::class);
                    $repository
                        ->expects($this->once())
                        ->method('findOneBy')
                        ->willReturnCallback(fn() => $user);

                    return $repository;
                }
            );

        return $manager;
    }
}
