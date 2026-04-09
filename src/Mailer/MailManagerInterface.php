<?php

namespace DMT\AuthenticationService\Mailer;

use DMT\AuthenticationService\Contracts\UserTokenEntity;

interface MailManagerInterface
{
    public function sendForgotPasswordLink(UserTokenEntity $userToken): void;
}
