# SpAntiCsrf

SpAntiCsrf is a lightweight PHP package that provides a simple and secure way to handle CSRF (Cross-Site Request Forgery) protection in your web applications. It uses token-based validation to ensure that requests are legitimate.

## Features

- **Session-based CSRF token management**: Tokens are stored securely in PHP sessions.
- **Token expiration**: Tokens expire after a configurable duration for enhanced security.
- **Validation and replay protection**: Ensures tokens are valid and prevents token reuse.
- **Session regeneration**: Provides a method to regenerate session IDs, helping to mitigate session fixation attacks when used appropriately.
- **One-time token usage**: Tokens are invalidated after successful validation to prevent reuse.

## Installation

You can install SpAntiCsrf using Composer:

```bash
composer require spencer14420/sp-anti-csrf
```

## Usage

### Generate a CSRF Token

Generate a token when rendering forms or making requests that require CSRF protection:

```php
use spencer14420\SpAntiCsrf\AntiCsrf;

$csrf = new AntiCsrf();
$token = $csrf->generateToken();
```

Use the token in your HTML form:

```html
<input
  type="hidden"
  id="csrf_token"
  name="csrf_token"
  value="<?php echo $token ?>"
/>
```

### Validate the Token

Validate the token on the server side when processing the form submission:

```php
use spencer14420\SpAntiCsrf\AntiCsrf;

$csrf = new AntiCsrf();

try {
    $token = $_POST['csrf_token'] ?? '';
    if (!$csrf->tokenIsValid($token)) {
        throw new Exception('Invalid CSRF token.');
    }
    // Proceed with processing the form
} catch (Exception $e) {
    // Handle invalid or expired token
    echo 'Error: ' . $e->getMessage();
}
```

### Regenerate the Session

For added security, you can regenerate the session ID periodically or certain actions:

```php
$csrf->regenerateSession();
```

- Consider calling `regenerateSession()` after sensitive actions like user login, logout, or privilege escalation to protect against session fixation attacks.

## API Reference

```php
generateToken(int $expirySeconds = 3600): string
```

Generates a new CSRF token, stores it in a session variable with an optional expiry time (default: 3600 seconds).

- **Parameters**:
  - `$expirySeconds`: The token's lifetime in seconds.
- **Returns**: The generated token as a string.

```php
tokenIsValid(string $tokenToCheck): bool
```

Validates a CSRF token.

- **Parameters**
  - `$tokenToCheck`: The token to validate.
- **Returns**: `true` if the token is valid and has not expired; `false` otherwise.

```php
regenerateSession(): void
```

Regenerates the PHP session ID to mitigate session fixation attacks.

```php
tokenIsNotExpired(): bool
```

Checks if the stored token has expired.

- **Returns**: `true` if the token has not expired; `false` otherwise.
