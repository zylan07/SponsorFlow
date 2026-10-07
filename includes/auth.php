<?php
/**
 * SponsorFlow - Authentication & Session Guard
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if a user is currently logged in.
 *
 * @return bool
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Returns currently logged-in user profile or null.
 *
 * @return array|null
 */
function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'staff'
    ];
}

/**
 * Guard: Requires authentication to view protected pages.
 * Redirects to login.php if not authenticated.
 */
function requireAuth(): void {
    if (!isLoggedIn()) {
        $currentUri = $_SERVER['REQUEST_URI'] ?? 'dashboard.php';
        $_SESSION['redirect_after_login'] = $currentUri;
        header('Location: login.php');
        exit;
    }
}

/**
 * Guard: Requires specific role(s).
 *
 * @param array|string $allowedRoles
 */
function requireRole($allowedRoles): void {
    requireAuth();
    $roles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
    $currentRole = $_SESSION['user_role'] ?? 'staff';

    if (!in_array($currentRole, $roles, true)) {
        http_response_code(403);
        die('<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>403 Forbidden</h1><p>You do not have permission to access this page.</p><p><a href="dashboard.php">Return to Dashboard</a></p></body></html>');
    }
}

/**
 * Establishes an authenticated session for a user.
 * Regenerates session ID to prevent session fixation attacks.
 *
 * @param array $user
 */
function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
    $_SESSION['logged_in_at'] = time();
}

/**
 * Terminates the authenticated session.
 */
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}
