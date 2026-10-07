<?php
/**
 * SponsorFlow - Secure Authentication Portal
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errorMessage = '';
$emailInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $emailInput = $email;

    if (empty($email) || empty($password)) {
        $errorMessage = 'Please provide both your email address and password.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                loginUser($user);
                $redirect = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
                unset($_SESSION['redirect_after_login']);
                setFlashMessage('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');
                header('Location: ' . $redirect);
                exit;
            } else {
                $errorMessage = 'Invalid email address or password. Please try again.';
            }
        } catch (PDOException $e) {
            $errorMessage = 'Database connection error: ' . $e->getMessage() . '. Please verify config/database.php.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log In · SponsorFlow</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="background: var(--bg);">
    <header class="public-nav">
        <a class="public-logo" href="index.php">SponsorFlow</a>
        <a class="btn sm ghost" href="index.php">&larr; Back to Home</a>
    </header>

    <div class="login-card card">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <h2>Sign in to SponsorFlow</h2>
            <p style="color: var(--mute); font-size: 0.9rem; margin-top: 0.25rem;">
                Enter your credentials to manage event sponsorships
            </p>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert error" style="margin-bottom: 1rem;">
                <?= e($errorMessage) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" novalidate>
            <?= csrf_field() ?>

            <label for="email">Email Address</label>
            <input id="email" name="email" type="email" value="<?= e($emailInput) ?>" placeholder="e.g. admin@sponsorflow.local" required autofocus>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="••••••••••••" required>

            <p style="margin-top: 1.5rem;">
                <button class="btn" type="submit" style="width: 100%;">Sign In</button>
            </p>
        </form>

        <div style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--line); font-size: 0.85rem; color: var(--mute);">
            <strong>Demo Accounts (from seed.sql):</strong>
            <ul style="margin: 0.5rem 0 0 1.25rem; padding: 0;">
                <li>Admin: <code>admin@sponsorflow.local</code> / <code>Admin@123</code></li>
                <li>Manager: <code>manager@sponsorflow.local</code> / <code>Manager@123</code></li>
                <li>Staff: <code>staff@sponsorflow.local</code> / <code>Staff@123</code></li>
            </ul>
        </div>
    </div>
</body>
</html>
