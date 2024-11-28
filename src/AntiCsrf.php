<?php

declare(strict_types=1);

namespace spencer14420\SpAntiCsrf;

class AntiCsrf
{
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
        $_SESSION['SpCsrfToken'] = [
            'value' => $token,
            'expiry' => time() + $expirySeconds
        ];
        return $token;
    }


    public function tokenIsValid(string $tokenToCheck): bool
    {
        $this->startSession();
        if (empty($_SESSION['SpCsrfToken']) || time() > $_SESSION['SpCsrfToken']['expiry']) {
            return false;
        }
        $isValid = hash_equals($_SESSION['SpCsrfToken']['value'], $tokenToCheck);
        if ($isValid) {
            unset($_SESSION['SpCsrfToken']);
        }
        return $isValid;
    }
}
