<?php

declare(strict_types=1);

namespace DMT\Test\AuthenticationService\Password;

use DMT\AuthenticationService\Password\NativePasswordHandler;
use PHPUnit\Framework\TestCase;

class NativePasswordHandlerTest extends TestCase
{
    public function testHash(): void
    {
        $password = 'password';

        $this->assertNotEquals($password, new NativePasswordHandler()->hash($password));
    }

    public function testVerify(): void
    {
        $passwordHash = new NativePasswordHandler()->hash('password');

        $this->assertTrue(new NativePasswordHandler()->verify('password', $passwordHash));
    }

    public function testNeedsNoRehash(): void
    {
        $passwordHash = new NativePasswordHandler()->hash('password');


        $this->assertFalse(new NativePasswordHandler()->needsRehash($passwordHash));
    }

    public function testNeedsRehash(): void
    {
        $passwordHash = password_hash('password', PASSWORD_BCRYPT, options: ['cost' => 10]);

        $this->assertTrue(new NativePasswordHandler()->needsRehash($passwordHash));
    }

    public function testRandomPassword(): void
    {
        $this->assertTrue(24 === mb_strlen(new NativePasswordHandler()->randomPassword(24)));
    }
}
