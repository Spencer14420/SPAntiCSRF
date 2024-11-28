<?php

declare(strict_types=1);

namespace spencer14420\SpAntiCsrf;

class AntiCsrf
{
    private const CSRF_TOKEN_KEY = 'SpCsrfToken';
    private const DEFAULT_EXPIRY_SECONDS = 3600;

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function regenerateSession(): void
    {
        $this->startSession();
        session_regenerate_id(true);
    }

    public function generateToken(int $expirySeconds = self::DEFAULT_EXPIRY_SECONDS): string
    {
        if ($expirySeconds <= 0) {
            throw new \InvalidArgumentException('Expiry time must be a positive integer.');
        }
        
        $this->startSession();
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::CSRF_TOKEN_KEY] = [
            'value' => $token,
            'expiry' => time() + $expirySeconds
        ];
        return $token;
    }
    private function getStoredToken(): ?array
    {
        return $_SESSION[self::CSRF_TOKEN_KEY] ?? null;
    }

    public function tokenIsNotExpired(): bool
    {
        $this->startSession();
        $token = $this->getStoredToken();
        if (empty($token) || time() > $token['expiry']) {
            unset($_SESSION[self::CSRF_TOKEN_KEY]);
            return false;
        }
        return true;
    }

    public function tokenIsValid(string $tokenToCheck): bool
    {
        if (empty($tokenToCheck)) {
            throw new \InvalidArgumentException('Token to check cannot be empty.');
        }
        
        $this->startSession();
        if (!$this->tokenIsNotExpired()) {
            return false;
        }
        
        $token = $this->getStoredToken();
        if (!hash_equals($token['value'], $tokenToCheck)) {
            return false;
        }
        
        // Invalidate the token after successful validation to prevent reuse
        // This is important for security because it prevents replay attacks,
        // where an attacker could reuse a valid token to perform unauthorized actions.
        unset($_SESSION[self::CSRF_TOKEN_KEY]);
        return true;
    }
}
