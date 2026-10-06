<?php

declare(strict_types=1);

namespace Nuartemo\Http;

final class Security
{
    public static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name('nuartemo_session');
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'path' => '/',
            ]);
            session_start();
        }
    }

    public static function csrfToken(): string
    {
        self::startSession();
        $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        self::startSession();
        return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function authenticated(): bool
    {
        self::startSession();
        return isset($_SESSION['id']) && is_numeric($_SESSION['id']);
    }

    public static function requireAuthentication(bool $json = false): void
    {
        if (self::authenticated()) {
            return;
        }

        http_response_code(401);
        if ($json) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => false, 'msg' => 'Autenticação necessária.']);
        } else {
            header('Location: login.php', true, 302);
        }
        exit;
    }

    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
