<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Session;

interface SessionHandlerInterface
{
    public const string USER_ID_KEY = 'authenticatedUserId';

    public function start(): void;

    public function login(int $userId): void;

    public function logout(): void;

    public function getAuthenticatedUserId(): ?int;
}
