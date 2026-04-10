<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Mailer;

use DMT\AuthenticationService\Contracts\TokenEntity;

interface MailManagerInterface
{
    public function sendForgotPasswordLink(string $email, TokenEntity $userToken): void;
}
