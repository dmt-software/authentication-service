<?php

namespace DMT\Test\AuthenticationService;

use DMT\AuthenticationService\AuthenticationService;
use DMT\AuthenticationService\Handlers\EmailPasswordHandler;
use DMT\AuthenticationService\Handlers\UserTokenHandler;
use DMT\AuthenticationService\Password\NativePasswordHandler;
use DMT\AuthenticationService\Session\SessionHandlerInterface;
use DMT\DependencyInjection\ContainerFactory;
use DMT\Test\AuthenticationService\Fixtures\User;
use DMT\Test\AuthenticationService\Fixtures\UserToken;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
{
    public function testAuthenticateWithUserCredentials(): void
    {
        $entityManager = $this->getEntityManager();

        $container = new ContainerFactory()->createContainer();
        $container->set(
            EmailPasswordHandler::class,
            fn (): EmailPasswordHandler => new EmailPasswordHandler(
                $entityManager,
                new NativePasswordHandler(),
                User::class,
            )
        );

        $sessionHandler = $this->createMock(SessionHandlerInterface::class);
        $sessionHandler
            ->expects($this->once())
            ->method('login')
            ->with(1);

        $service = $container->get(
            AuthenticationService::class,
            $entityManager,
            $sessionHandler,
            User::class
        );

        $credentials = [
            'email' => 'user@example.com',
            'password' => 'password'
        ];

        $service->authenticate(EmailPasswordHandler::class, $credentials, true);
    }

    public function testAuthenticateWithUserToken(): void
    {

        $entityManager = $this->getEntityManager();

        $container = new ContainerFactory()->createContainer();
        $container->set(
            UserTokenHandler::class,
            fn (): UserTokenHandler => new UserTokenHandler(
                $entityManager,
                UserToken::class,
            )
        );

        $sessionHandler = $this->createMock(SessionHandlerInterface::class);
        $sessionHandler
            ->expects($this->never())
            ->method('login');

        $service = $container->get(
            AuthenticationService::class,
            $entityManager,
            $sessionHandler,
            User::class
        );

        $credentials = [
            'token' => '8382a32ab3',
            'reason' => 'activate',
        ];

        $service->authenticate(UserTokenHandler::class, $credentials);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager
            ->expects($this->any())
            ->method('find')
            ->willReturnCallback(function (string $entity, array $criteria) {
                if ($entity === User::class) {
                    $user = new User();
                    $user->id = 1;
                    $user->email = $criteria['email'] ?? null;
                    $user->password = password_hash('password', PASSWORD_DEFAULT);

                    return $user;
                }

                if ($entity === UserToken::class) {
                    $token = new UserToken();
                    $token->id = 1;
                    $token->token = $criteria['token'] ?? null;
                    $token->reason = $criteria['reason'] ?? null;
                    $token->user = new User();

                    return $token;
                }

                return null;
            });

        return $manager;
    }
}
