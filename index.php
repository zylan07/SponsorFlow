<?php
/**
 * SponsorFlow - Landing Page
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$isUserLoggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SponsorFlow · Event Sponsorship & Partnership Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="public-nav">
        <a class="public-logo" href="index.php">SponsorFlow</a>
        <div>
            <?php if ($isUserLoggedIn): ?>
                <a class="btn sm" href="dashboard.php">Go to Dashboard</a>
            <?php else: ?>
                <a class="btn sm ghost" href="login.php">Sign In</a>
                <a class="btn sm" href="login.php">Access Dashboard</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="hero">
        <h1>Every sponsor, from first email to final deliverable.</h1>
        <p>SponsorFlow replaces scattered spreadsheets, lost email threads, and untracked WhatsApp reminders with a single, database-driven sponsorship CRM built for event organizers.</p>
        
        <p>
            <?php if ($isUserLoggedIn): ?>
                <a class="btn" href="dashboard.php" style="padding: 0.75rem 1.5rem; font-size: 1.05rem;">Open Dashboard &rarr;</a>
            <?php else: ?>
                <a class="btn" href="login.php" style="padding: 0.75rem 1.5rem; font-size: 1.05rem;">Access SponsorFlow &rarr;</a>
            <?php endif; ?>
        </p>

        <div class="flow" aria-label="Sponsorship lifecycle">
            <span>Prospect</span>
            <span>Outreach</span>
            <span>Follow-up</span>
            <span>Proposal</span>
            <span>Negotiation</span>
            <span>Confirmed</span>
            <span>Payment</span>
            <span>Deliverables</span>
            <span>Reporting</span>
        </div>
    </section>

    <section class="sec">
        <h2>The Problem Event Organizers Face</h2>
        <p style="color: var(--mute); font-size: 1.05rem;">
            Student symposiums, college hackathons, and corporate conferences suffer from operational chaos. Sponsor contacts sit in one spreadsheet, follow-up promises in WhatsApp messages, proposal PDF versions get lost, and promised logo placements slip through the cracks.
        </p>
    </section>

    <section class="sec">
        <h2>How SponsorFlow Solves It</h2>
        <div class="grid3">
            <div class="card">
                <h3>Structured Pipeline</h3>
                <p style="color: var(--mute);">Track companies across 7 distinct stages: from cold prospect to signed contracts and paid sponsors with real-time financial tracking.</p>
            </div>
            <div class="card">
                <h3>Communication & Follow-ups</h3>
                <p style="color: var(--mute);">Log emails, calls, and meetings. Automated date calculation flags overdue, due-today, and upcoming tasks so no sponsor is forgotten.</p>
            </div>
            <div class="card">
                <h3>Deliverables Accountability</h3>
                <p style="color: var(--mute);">Ensure banners, booth spaces, workshop slots, and social media promotions are delivered on schedule with designated owners.</p>
            </div>
        </div>
    </section>

    <footer class="sec" style="text-align: center; border-top: 1px solid var(--line); margin-top: 3rem; color: var(--mute);">
        <p><strong>SponsorFlow</strong> · Web Technology Lab Project · Full-Stack PHP & MySQL Architecture</p>
        <p><small>&copy; <?= date('Y') ?> SponsorFlow. All corporate names shown are fictional sample data for demonstration.</small></p>
    </footer>
</body>
</html>
