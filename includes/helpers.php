<?php
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function validateCsrfToken(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    unset($_SESSION['csrf_token']);
    return $valid;
}
