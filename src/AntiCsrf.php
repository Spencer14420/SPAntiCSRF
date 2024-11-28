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

    private function regenerateSession(): void
    {
        // Regenerate session ID if it's been more than 5 minutes since the last regeneration
        $lastRegenerated = $_SESSION['last_regenerated'] ?? 0;
        if (time() - $lastRegenerated > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regenerated'] = time();
        }
    }

    public function generateToken(int $expirySeconds = 3600): string
    {
        $this->startSession();
        $this->regenerateSession();
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::CSRF_TOKEN_KEY] = [
            'value' => $token,
            'expiry' => time() + $expirySeconds
        ];
        return $token;
    }

    private function getToken(): ?array
    {
        return $_SESSION[self::CSRF_TOKEN_KEY] ?? null;
    }

    public function tokenHasExpired(): bool
    {
        $this->startSession();
        $token = $this->getToken();
        return empty($token) || time() > $token['expiry'];
    }

    public function tokenIsValid(string $tokenToCheck): bool
    {
        $this->startSession();
        $this->regenerateSession();
        if ($this->tokenHasExpired()) {
            return false;
        }
        $token = $this->getToken();
        $isValid = hash_equals($token['value'], $tokenToCheck);
        if ($isValid) {
            // Invalidate the token after successful validation to prevent reuse
            unset($_SESSION[self::CSRF_TOKEN_KEY]);
        }
        return $isValid;
    }
}
