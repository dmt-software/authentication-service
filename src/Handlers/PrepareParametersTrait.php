<?php

namespace DMT\AuthenticationService\Handlers;

use BackedEnum;
use ReflectionException;
use ReflectionProperty;

trait PrepareParametersTrait
{
    private function prepareParameters(array $parameters, string $tokenEntity): array
    {
        $reasonPropertyType = new ReflectionProperty($tokenEntity, 'reason')->getType();

        if (is_subclass_of($reasonPropertyType->getName(), BackedEnum::class)) {
            /** @var BackedEnum $enum */
            $enum = $reasonPropertyType->getName();

            if (is_scalar($parameters['reason'])) {
                $parameters['reason'] = $enum::tryFrom($parameters['reason']);
            }
        } elseif ($reasonPropertyType->isBuiltin()) {
            settype($parameters['reason'], $reasonPropertyType->getName());
        } else {
            throw new ReflectionException('Invalid type for reason property');
        }

        return $parameters;
    }
}
