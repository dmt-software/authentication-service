<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Event\Subscribers;

use DMT\AuthenticationService\Event\Model\AuthenticatedUser;
use DMT\AuthenticationService\Event\Model\ValidatedToken;
use DMT\AuthenticationService\Session\SessionHandlerInterface;
use ReflectionProperty;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PersistAuthenticatedUserEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SessionHandlerInterface $sessionHandler,
    ) {
    }

    public function saveAuthenticatedUser(AuthenticatedUser|ValidatedToken $model): void
    {
        if ($model->persist) {
            $userId = new ReflectionProperty($model->user, 'id')
                ->getValue($model->user);

            $this->sessionHandler->login($userId);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticatedUser::class => ['saveAuthenticatedUser', 100],
            ValidatedToken::class => ['saveAuthenticatedUser', 100],
        ];
    }
}
