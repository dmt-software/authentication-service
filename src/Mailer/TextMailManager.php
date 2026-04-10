<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Mailer;

use DMT\AuthenticationService\Contracts\TokenEntity;
use DMT\DependencyInjection\Attributes\ConfigValue;
use DMT\MailService\MailService;
use DMT\MailService\Model\EmailAddress;
use DMT\MailService\Model\EmailMessage;

final readonly class TextMailManager implements MailManagerInterface
{
    public function __construct(
        private MailService $mailService,
        #[ConfigValue('mailer.sender', '')]
        private string $from,
        #[ConfigValue('app.url', '')]
        private string $siteUrl,
    ) {
    }

    public function sendForgotPasswordLink(string $email, TokenEntity $userToken): void
    {
        $text = "A request has been made to reset your password\r\n\r\n";
        $text .= "please click on the following link\r\n\r\n";
        $text .= "%s/reset-password/%s\r\n\r\n";
        $text .= "If you did not request a password reset, please ignore this email\r\n";

        $message = new EmailMessage(
            subject: 'forgot password',
            text: sprintf($text, $this->siteUrl, $userToken->token),
            to: new EmailAddress($email),
            from: new EmailAddress($this->from),
        );

        $this->mailService->send($message);
    }
}
