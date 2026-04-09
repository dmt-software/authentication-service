<?php

namespace DMT\AuthenticationService\Mailer;

use DMT\DependencyInjection\Attributes\ConfigValue;
use DMT\MailService\MailService;
use DMT\MailService\Model\EmailAddress;
use DMT\MailService\Model\TemplatedMessage;

final readonly class MailManager implements MailManagerInterface
{
    public function __construct(
        private MailService $mailService,
        #[ConfigValue('mailer.sender', '')]
        private string $from
    ) {
    }

    public function sendForgotPasswordLink(object $userToken): void
    {
        $message = new TemplatedMessage(
            subject: 'forgot password',
            template: 'mail/forgot-password.twig',
            to: new EmailAddress($userToken->user->email, $userToken->user->getFullName()),
            from: new EmailAddress($this->from),
            context: compact('userToken'),
        );

        $this->mailService->send($message);
    }
}
