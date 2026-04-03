<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Password;

use SensitiveParameter;

interface PasswordHandlerInterface
{
    public function hash(#[SensitiveParameter] string $plainPassword): string;

    public function verify(#[SensitiveParameter] string $plainPassword, string $passwordHash): bool;

    public function needsRehash(#[SensitiveParameter] string $passwordHash): bool;

    public function randomPassword(int $length = 8): string;
}
