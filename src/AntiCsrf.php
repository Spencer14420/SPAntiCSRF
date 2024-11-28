<?php

declare(strict_types=1);

namespace spencer14420\SpAntiCsrf;

class AntiCsrf
{
    private const CSRF_TOKEN_KEY = 'SpCsrfToken';

    private function startAndRegenerateSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenerate session ID if it's been more than 5 minutes since the last regeneration
        $lastRegenerated = $_SESSION['last_regenerated'] ?? 0;
        if (time() - $lastRegenerated > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regenerated'] = time();
        }
    }

    public function generateToken(int $expirySeconds = 3600): string
    {
        $this->startAndRegenerateSession();
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::CSRF_TOKEN_KEY] = [
            'value' => $token,
            'expiry' => time() + $expirySeconds
        ];
        return $token;
    }

    public function tokenHasExpired(): bool
    {
        $this->startAndRegenerateSession();
        return empty($_SESSION[self::CSRF_TOKEN_KEY]) || time() > $_SESSION[self::CSRF_TOKEN_KEY]['expiry'];
    }

    public function tokenIsValid(string $tokenToCheck): bool
    {
        $this->startAndRegenerateSession();
        if ($this->tokenHasExpired()) {
            return false;
        }
        $isValid = hash_equals($_SESSION[self::CSRF_TOKEN_KEY]['value'], $tokenToCheck);
        if ($isValid) {
            // Invalidate the token after successful validation to prevent reuse
            unset($_SESSION[self::CSRF_TOKEN_KEY]);
        }
        return $isValid;
    }
}
