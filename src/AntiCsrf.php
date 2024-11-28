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

    public function regenerateSession(): void
    {
        $this->startSession();
        session_regenerate_id(true);
    }

    public function generateToken(int $expirySeconds = 3600): string
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

    private function getToken(): ?array
    {
        return $_SESSION[self::CSRF_TOKEN_KEY] ?? null;
    }

    public function tokenIsActive(): bool
    {
        $this->startSession();
        $token = $this->getToken();
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
        if (!$this->tokenIsActive()) {
            return false;
        }
        
        $token = $this->getToken();
        if (!hash_equals($token['value'], $tokenToCheck)) {
            return false;
        }
        
        // Invalidate the token after successful validation to prevent reuse
        unset($_SESSION[self::CSRF_TOKEN_KEY]);
        return true;
    }
}
