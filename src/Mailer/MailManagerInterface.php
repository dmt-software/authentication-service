<?php

namespace DMT\AuthenticationService\Mailer;

interface MailManagerInterface
{
    public function sendForgotPasswordLink(object $userToken): void;
}
