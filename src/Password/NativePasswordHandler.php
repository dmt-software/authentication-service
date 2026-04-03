<?php

namespace DMT\AuthenticationService\Password;

use DMT\AuthenticationService\Password\PasswordHandlerInterface;
use SensitiveParameter;

class NativePasswordHandler implements PasswordHandlerInterface
{

    public function hash(#[SensitiveParameter] string $plainPassword): string
    {
        // TODO: Implement hash() method.
    }

    public function verify(#[SensitiveParameter] string $plainPassword, string $passwordHash): bool
    {
        // TODO: Implement verify() method.
    }

    public function needsRehash(#[SensitiveParameter] string $passwordHash): bool
    {
        // TODO: Implement needsRehash() method.
    }

    public function randomPassword(int $length = 8): string
    {
        // TODO: Implement randomPassword() method.
    }
}