<?php

declare(strict_types=1);

namespace spencer14420\SpAntiCsrf;

class AntiCsrf
{
    private const CSRF_TOKEN_KEY = 'SpCsrfToken';

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function generateToken(int $expirySeconds = 3600): string
    {
        $this->startSession();
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::CSRF_TOKEN_KEY] = [
            'value' => $token,
            'expiry' => time() + $expirySeconds
        ];
        return $token;
    }

    public function tokenHasExpired(): bool
    {
        $this->startSession();
        return empty($_SESSION[self::CSRF_TOKEN_KEY]) || time() > $_SESSION[self::CSRF_TOKEN_KEY]['expiry'];
    }

    public function tokenIsValid(string $tokenToCheck): bool
    {
        $this->startSession();
        if ($this->tokenHasExpired()) {
            return false;
        }
        $isValid = hash_equals($_SESSION[self::CSRF_TOKEN_KEY]['value'], $tokenToCheck);
        if ($isValid) {
            unset($_SESSION[self::CSRF_TOKEN_KEY]);
        }
        return $isValid;
    }
}
