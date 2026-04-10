<?php

declare(strict_types=1);

namespace DMT\Test\AuthenticationService\Fixtures;

enum TokenType: string
{
    case Activate = 'activate';
    case ResetPassword = 'reset-password';
    case ForgotPassword = 'forgot-password';
}
