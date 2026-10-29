<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(string|int|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $msg): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'msg' => $msg,
    ];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $message = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $message;
}

function csrfToken(): string
{
    if (!isset($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function hasValidCsrfToken(mixed $token): bool
{
    $sessionToken = $_SESSION['_csrf_token'] ?? null;

    return is_string($token)
        && is_string($sessionToken)
        && hash_equals($sessionToken, $token);
}
