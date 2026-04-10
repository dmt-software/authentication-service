<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Mailer;

use DMT\AuthenticationService\Contracts\TokenEntity;
use DMT\DependencyInjection\Attributes\ConfigValue;
use DMT\MailService\MailService;
use DMT\MailService\Model\EmailAddress;
use DMT\MailService\Model\TemplatedMessage;

final readonly class HtmlMailManager implements MailManagerInterface
{
    public function __construct(
        private MailService $mailService,
        #[ConfigValue('mailer.sender', '')]
        private string $from
    ) {
    }

    public function sendForgotPasswordLink(string $email, TokenEntity $userToken): void
    {
        $message = new TemplatedMessage(
            subject: 'forgot password',
            template: 'mail/forgot-password.twig',
            to: new EmailAddress($email),
            from: new EmailAddress($this->from),
            context: compact('userToken'),
        );

        $this->mailService->send($message);
    }
}
