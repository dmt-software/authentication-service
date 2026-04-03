<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Middlewares;

use DMT\AuthenticationService\AuthenticationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Environment;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthenticationService $service,
        private Environment $twig,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->service->getAuthenticatedUser();

        if ($user !== null) {
            $this->twig->addGlobal('user', $user);

            $request = $request->withAttribute('user', $user);
        }

        return $handler->handle($request);
    }
}
