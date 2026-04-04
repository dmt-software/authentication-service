<?php

namespace DMT\Test\AuthenticationService\Handlers\Token;

use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\Token\UserTokenAuthenticationHandler;
use DMT\Test\AuthenticationService\Fixtures\User;
use DMT\Test\AuthenticationService\Fixtures\UserToken;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UserTokenHandlerTest extends TestCase
{
    public function testAuthenticate(): void
    {
        $token = new UserToken();
        $token->id = 1;
        $token->token = '69c67c86054e3';
        $token->reason = 'activate';
        $token->user = new User();

        $handler = new UserTokenAuthenticationHandler($this->getEntityManagerForUserToken($token), UserToken::class);
        $handler->authenticate(['token' => '69c67c86054e3', 'reason' => 'activate']);
    }

    public function testAuthenticateWithInvalidToken(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid token.');

        $token = new UserToken();
        $token->token = '7c8669c6054e3';
        $token->reason = 'forgot-password';
        $token->user = new User();

        $handler = new UserTokenAuthenticationHandler($this->getEntityManagerForUserToken($token), UserToken::class);
        $handler->authenticate(['token' => '7c8669c6054e3', 'reason' => 'forgot-password']);
    }

    public function testAuthenticateWithUnknownToken(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid token.');

        $handler = new UserTokenAuthenticationHandler($this->getEntityManagerForUserToken(null), UserToken::class);
        $handler->authenticate(['token' => '7c8669c6054e3', 'reason' => 'forgot-password']);
    }

    private function getEntityManagerForUserToken(?UserToken $userToken): EntityManagerInterface
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('find')
            ->with(UserToken::class)
            ->willReturnCallback(fn() => $userToken);

        return $manager;
    }
}
