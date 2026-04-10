<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Controllers;

use DMT\AuthenticationService\AuthenticationService;
use DMT\AuthenticationService\Exceptions\AuthenticationException;
use DMT\RoutingService\Attributes as DMT;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

#[DMT\RouteGroup(path: '')]
class AuthenticationController
{
    public function __construct(
        private AuthenticationService $authenticationService,
        private Environment $twig,
    ) {
    }

    #[DMT\Route(method: ['GET', 'POST'], path: '/login', name: 'login')]
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($request->getMethod() === 'GET') {
            return $this->render($response, 'authentication/login.twig');
        }

        $data = array_map(trim(...), (array) $request->getParsedBody());

        if (array_key_exists('email', $data)) {
            $data['email'] = strtolower($data['email']);
        }

        try {
            $this->authenticationService->authenticate($data, true);

            return $response
                ->withHeader('Location', '/')
                ->withStatus(302);
        } catch (AuthenticationException) {
            $error = 'Login failed';
        }

        return
            $this->render(
                $response->withStatus(400),
                'authentication/login.twig',
                compact('error')
            );
    }

    #[DMT\Route(method: ['GET'], path: '/logout', name: 'logout')]
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->authenticationService->clear();

        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    #[DMT\Route(method: ['GET', 'POST'], path: '/forgot-password', name: 'forgot-password')]
    public function forgotPassword(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($request->getMethod() === 'GET') {
            return $this->render($response, 'authentication/forgot-password.twig');
        }

        $email = array_map(trim(...), (array) $request->getParsedBody())['email'] ?? '';

        try {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid email address');
            }

            $this->authenticationService->forgotPassword($email);

            return $this->render(
                $response,
                'authentication/forgot-password.twig',
                ['success' => 'Reset password link has been sent']
            );
        } catch (InvalidArgumentException $exception) {
            $error = $exception->getMessage();
        }

        return
            $this->render(
                $response->withStatus(400),
                'authentication/login.twig',
                compact('error')
            );
    }

    #[DMT\Route(method: ['GET', 'POST'], path: '/reset-password/{token}', name: 'reset-password')]
    public function resetPassword(
        ServerRequestInterface $request,
        ResponseInterface $response,
        ...$args
    ): ResponseInterface {
        $args = array_merge(...$args) ?: $request->getAttributes();
        $token = trim((string) ($args['token'] ?? ''));
        $reason = 'forgot-password';

        try {
            $this->authenticationService->authenticateByToken(compact('token', 'reason'));

            return $this->render($response, 'authentication/reset-password.twig');
        } catch (AuthenticationException) {
            $error = 'Reset password link is invalid or has expired.';
        }

        return $this->render(
            $response->withStatus(401),
            'authentication/reset-password.twig',
            compact('error')
        );
    }

    #[DMT\Route(method: ['POST'], path: '/reset-password/{token}')]
    public function changePassword(
        ServerRequestInterface $request,
        ResponseInterface $response,
        ...$args
    ): ResponseInterface {
        $args = array_merge(...$args) ?: $request->getAttributes();
        $parameters = array_map(trim(...), (array)$request->getParsedBody());

        $token = trim((string) ($args['token'] ?? ''));
        $password = $parameters['password'] ?? '';
        $retypePassword = $parameters['retype'] ?? '';

        try {
            if (empty($password) || $password !== $retypePassword) {
                throw new InvalidArgumentException('Password and retype do not match');
            }

            $this->authenticationService->resetPassword($token, $password);

            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        } catch (InvalidArgumentException $exception) {
            return $this->render(
                $response->withStatus(400),
                'authentication/reset-password.twig',
                ['error' => $exception->getMessage()]
            );
        } catch (AuthenticationException) {
            $error = 'Reset password link is invalid or has expired.';
        }

        return $this->render(
            $response->withStatus(401),
            'authentication/reset-password.twig',
            compact('error')
        );
    }

    private function render(ResponseInterface $response, string $template, array $context = []): ResponseInterface
    {
        $response->getBody()->write($this->twig->render($template, $context));
        $response->getBody()->rewind();

        return $response;
    }
}
