<?php
/**
 * SponsorFlow - CSRF Protection Utilities
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generates or retrieves the current CSRF token.
 *
 * @return string
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Returns an HTML hidden input field containing the CSRF token.
 *
 * @return string
 */
function csrf_field(): string {
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verifies a submitted CSRF token.
 *
 * @param string|null $token
 * @return bool
 */
function csrf_verify(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Enforces CSRF token check on POST/PUT/DELETE requests.
 * Exits with 403 response if verification fails.
 */
function csrf_protect(): void {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        
        // Check JSON input if body is application/json
        if (!$token && stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $token = $input['csrf_token'] ?? null;
        }

        if (!csrf_verify($token)) {
            http_response_code(403);
            if (stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false ||
                stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'error'   => 'CSRF token validation failed. Please refresh the page and try again.'
                ]);
            } else {
                echo '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body>' .
                     '<h1>403 Forbidden</h1>' .
                     '<p>Security check failed (invalid or missing CSRF token). Please <a href="javascript:history.back()">go back</a> and refresh the page.</p>' .
                     '</body></html>';
            }
            exit;
        }
    }
}
