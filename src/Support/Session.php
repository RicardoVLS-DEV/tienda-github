<?php
declare(strict_types=1);

namespace App\Support;

final class Session
{
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
            session_start();
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function login(array $user): void
    {
        session_regenerate_id(true);
        $this->set('user', ['id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email']]);
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public function user(): ?array
    {
        return $this->get('user');
    }

    public function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public function pullFlash(): ?array
    {
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }
}
