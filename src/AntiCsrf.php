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

    //Sets and echoes a "SpCsrfToken" session variable
    public function storeAndGetToken()
    {
        $this->startSession();
        $token
            = bin2hex(random_bytes(32));
        $_SESSION['SpCsrfToken'] = $token;
        echo $token;
    }

    public function tokenIsValid($tokenToCheck)
    {
        if (empty($_SESSION['SpCsrfToken'])) {
            return false;
        }
        return hash_equals($_SESSION['SpCsrfToken'], $tokenToCheck);
    }
}
