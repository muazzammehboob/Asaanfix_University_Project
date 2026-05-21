<?php
/**
 * AsaanFix Pakistan - CSRF Protection
 */

/**
 * Generate CSRF token
 */
function generateCSRFToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Get CSRF hidden input field
 */
function csrfField(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCSRFToken() . '">';
}

/**
 * Validate CSRF token
 */
function validateCSRFToken(): bool {
    $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    $valid = hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    // Regenerate after validation
    unset($_SESSION[CSRF_TOKEN_NAME]);
    return $valid;
}

/**
 * Require valid CSRF token or die
 */
function requireCSRF(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !validateCSRFToken()) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            jsonResponse(['success' => false, 'message' => 'Invalid security token. Please refresh.'], 403);
        }
        setFlash('danger', 'Invalid security token. Please try again.');
        redirectBack();
    }
}
