<?php
/**
 * SponsorFlow - Application Sidebar Navigation
 */
if (!isset($activePage)) {
    $activePage = basename($_SERVER['PHP_SELF'], '.php');
}

$user = currentUser() ?? [
    'name'  => 'Guest',
    'role'  => 'staff',
    'email' => ''
];

$navItems = [
    'dashboard'     => ['Dashboard', 'dashboard.php'],
    'companies'     => ['Companies', 'companies.php'],
    'contacts'      => ['Contacts', 'contacts.php'],
    'outreach'      => ['Outreach', 'outreach.php'],
    'followups'     => ['Follow-ups', 'followups.php'],
    'proposals'     => ['Proposals', 'proposals.php'],
    'sponsorships'  => ['Sponsorships', 'sponsorships.php'],
    'deliverables'  => ['Deliverables', 'deliverables.php'],
    'reports'       => ['Reports', 'reports.php'],
];
?>
<aside class="side" id="side">
    <a class="logo" href="dashboard.php">
        SponsorFlow
        <span class="logo-tag">CRM</span>
    </a>
    
    <nav aria-label="Main Navigation">
        <?php foreach ($navItems as $key => $item): ?>
            <a href="<?= e($item[1]) ?>" class="<?= ($activePage === $key) ? 'on' : '' ?>">
                <span><?= e($item[0]) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="side-footer">
        <div class="user-snippet">
            <div class="user-avatar" title="<?= e($user['email']) ?>">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?= e($user['name']) ?></div>
                <div class="user-role"><?= e($user['role']) ?></div>
            </div>
        </div>
        <a href="logout.php" class="btn sm secondary" style="width: 100%; text-align: center; display: block;">Log out</a>
    </div>
</aside>
