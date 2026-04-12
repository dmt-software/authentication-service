<?php

declare(strict_types=1);

namespace DMT\Test\AuthenticationService\Handlers\PublicProperty;

use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\AuthenticationService\Handlers\Accessor\TokenAuthenticationHandler;
use DMT\Test\AuthenticationService\Fixtures\User;
use DMT\Test\AuthenticationService\Fixtures\Token;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class UserTokenHandlerTest extends TestCase
{
    public function testAuthenticate(): void
    {
        $token = new Token();
        $token->id = 1;
        $token->token = '69c67c86054e3';
        $token->reason = 'activate';
        $token->user = new User();

        $handler = new TokenAuthenticationHandler($this->getEntityManagerForUserToken($token), Token::class);
        $handler->authenticate(['token' => '69c67c86054e3', 'reason' => 'activate']);
    }

    public function testAuthenticateWithInvalidToken(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid token.');

        $token = new Token();
        $token->token = '7c8669c6054e3';
        $token->reason = 'forgot-password';
        $token->user = new User();

        $handler = new TokenAuthenticationHandler($this->getEntityManagerForUserToken($token), Token::class);
        $handler->authenticate(['token' => '7c8669c6054e3', 'reason' => 'forgot-password']);
    }

    public function testAuthenticateWithUnknownToken(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid token.');

        $handler = new TokenAuthenticationHandler($this->getEntityManagerForUserToken(null), Token::class);
        $handler->authenticate(['token' => '7c8669c6054e3', 'reason' => 'forgot-password']);
    }

    private function getEntityManagerForUserToken(?Token $userToken): EntityManagerInterface
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Token::class)
            ->willReturnCallback(
                function () use ($userToken) {
                    $repository = $this->createMock(EntityRepository::class);
                    $repository
                        ->expects($this->once())
                        ->method('findOneBy')
                        ->willReturnCallback(fn() => $userToken);

                    return $repository;
                }
            );


        return $manager;
    }
}
