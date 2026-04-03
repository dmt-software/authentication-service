<?php

namespace DMT\AuthenticationService\Handlers;

interface SessionHandlerInterface
{
    public const string USER_ID_KEY = 'authenticatedUserId';

    public function getAuthenticatedUserId(): ?int;
}
