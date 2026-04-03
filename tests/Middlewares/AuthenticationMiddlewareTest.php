<?php

namespace DMT\Test\DMT\AuthenticationService\Middlewares;

use DMT\AuthenticationService\AuthenticationService;
use DMT\AuthenticationService\AuthenticationServiceInterface;
use DMT\AuthenticationService\Middlewares\AuthenticationMiddleware;
use DMT\Test\AuthenticationService\Fixtures\User;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class AuthenticationMiddlewareTest extends TestCase
{
    public function testProcessMiddleware(): void
    {
        $user = new User();
        $user->id = 12;
        $user->email = 'user@example.com';

        $service = $this->createMock(AuthenticationServiceInterface::class);
        $service
            ->expects($this->once())
            ->method('getAuthenticatedUser')
            ->willReturn($user);

        $engine = $this->createMock(Environment::class);
        $engine
            ->expects($this->once())
            ->method('addGlobal')
            ->with('user', $user);

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler
            ->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (ServerRequestInterface $request) {
                $this->assertInstanceOf(User::class, $request->getAttribute('user'));

                return true;
            }))
            ->willReturn(new Response());

        $middleware = new AuthenticationMiddleware($service, $engine);
        $middleware->process(new ServerRequest('GET', '/'), $requestHandler);
    }
}
