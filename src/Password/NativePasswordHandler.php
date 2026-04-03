<?php

namespace DMT\AuthenticationService\Password;

use SensitiveParameter;

class NativePasswordHandler implements PasswordHandlerInterface
{
    public function hash(#[SensitiveParameter] string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function verify(#[SensitiveParameter] string $plainPassword, string $passwordHash): bool
    {
        return password_verify($plainPassword, $passwordHash);
    }

    public function needsRehash(#[SensitiveParameter] string $passwordHash): bool
    {
        return password_needs_rehash($passwordHash, PASSWORD_DEFAULT);
    }

    public function randomPassword(int $length = 16): string
    {
        return substr(password_hash(uniqid(), PASSWORD_DEFAULT), -$length);
    }
}
