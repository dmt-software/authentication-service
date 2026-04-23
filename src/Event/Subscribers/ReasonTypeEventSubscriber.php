<?php

namespace DMT\AuthenticationService\Event\Subscribers;

use BackedEnum;
use DMT\AuthenticationService\Event\Model\AccessToken;
use DMT\AuthenticationService\Event\Model\CreateToken;
use DMT\DependencyInjection\Attributes\ConfigValue;
use ReflectionProperty;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReasonTypeEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[ConfigValue('authentication.token', 'DMT\Entity\UserToken')]
        private string $tokenEntity
    ) {
    }

    public function fixReasonPropertyType(AccessToken|CreateToken $model): void
    {
        $reasonPropertyType = new ReflectionProperty($this->tokenEntity, 'reason')->getType();

        if (is_scalar($model->reason) && $reasonPropertyType->isBuiltin()) {
            return;
        }

        if (
            is_scalar($model->reason)
            && is_subclass_of($reasonPropertyType->getName(), BackedEnum::class)
        ) {
            /** @var BackedEnum $enum */
            $enum = $reasonPropertyType->getName();

            $model->reason = $enum::tryFrom($model->reason);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AccessToken::class => ['fixReasonPropertyType', 255],
            CreateToken::class => ['fixReasonPropertyType', 255],
        ];
    }
}