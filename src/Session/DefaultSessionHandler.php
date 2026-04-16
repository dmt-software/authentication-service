<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Session;

use DMT\DependencyInjection\Attributes\ConfigValue;

class DefaultSessionHandler implements SessionHandlerInterface
{
    public function __construct(
        #[ConfigValue('authentication.session.secure', true)]
        bool $secure = false,
        #[ConfigValue('authentication.session.ttl', 1800)]
        int $lifetime = 1800
    ) {
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'httponly' => true,
            'secure' => $secure,
            'samesite' => 'Lax',
        ]);

        ini_set('session.use_strict_mode', '1');
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_start();
    }

    public function login(int $userId): void
    {
        $this->start();

        session_regenerate_id(true);

        $_SESSION[self::USER_ID_KEY] = $userId;

        session_write_close();
    }

    public function logout(): void
    {
        $this->start();

        $_SESSION = [];

        if (ini_get('session.use_cookies') === '1') {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ],
            );
        }

        session_destroy();
    }

    public function getAuthenticatedUserId(): ?int
    {
        $this->start();

        $userId = $_SESSION[self::USER_ID_KEY] ?? null;

        return is_int($userId) ? $userId : null;
    }
}
