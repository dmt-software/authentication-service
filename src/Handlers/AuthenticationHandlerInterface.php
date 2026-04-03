<?php

namespace DMT\AuthenticationService;

interface AuthenticationHandlerInterface
{
    public function authenticate(...$credentials): object;
}
