<?php
/**
 * SponsorFlow - Application Header Shell
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';

// Ensure user is authenticated for all standard application pages
requireAuth();

$pageTitle = $pageTitle ?? 'SponsorFlow CRM';
$user = currentUser();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> · SponsorFlow</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <header class="top-bar">
            <h1 class="page-title"><?= e($pageTitle) ?></h1>
            <div class="top-bar-actions">
                <span class="user-role badge"><?= e(ucfirst($user['role'] ?? 'User')) ?></span>
                <span style="font-size: 0.9rem; color: var(--mute);">Signed in as <strong><?= e($user['name'] ?? 'User') ?></strong></span>
                <a href="logout.php" class="btn sm ghost">Sign Out</a>
            </div>
        </header>

        <main>
            <?php if ($flash): ?>
                <div class="alert <?= e($flash['type']) ?>" data-flash-msg="<?= e($flash['message']) ?>">
                    <span><?= e($flash['message']) ?></span>
                    <button type="button" class="btn sm ghost" onclick="this.parentElement.remove()" style="padding: 2px 6px;">×</button>
                </div>
            <?php endif; ?>
