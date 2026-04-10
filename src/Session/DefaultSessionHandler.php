<?php

declare(strict_types=1);

namespace DMT\AuthenticationService\Session;

class DefaultSessionHandler implements SessionHandlerInterface
{
    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Lax',
        ]);

        ini_set('session.use_strict_mode', '1');

        session_start();
    }

    public function login(int $userId): void
    {
        $this->start();

        session_regenerate_id(true);

        $_SESSION[self::USER_ID_KEY] = $userId;
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
