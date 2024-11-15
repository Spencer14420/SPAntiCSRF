<?php

namespace spencer14420\SpAntiCsrf;

class AntiCSRF
{
    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    //Sets and returns a "SpCsrfToken" session variable
    public function generateToken()
    {
        $this->startSession();
        $token
            = bin2hex(random_bytes(32));
        $_SESSION['SpCsrfToken'] = $token;
        return $token;
    }

    public function tokenIsValid($tokenToCheck)
    {
        $this->startSession();
        if (empty($_SESSION['SpCsrfToken'])) {
            return false;
        }
        return hash_equals($_SESSION['SpCsrfToken'], $tokenToCheck);
    }
}
