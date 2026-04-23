<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Event;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AuthenticationServiceEventDispatcher extends EventDispatcher
{
    public function __construct(EventSubscriberInterface ...$eventSubscribers)
    {
        parent::__construct();

        foreach ($eventSubscribers as $eventSubscriber) {
            $this->addSubscriber($eventSubscriber);
        }
    }
}
