<?php
/**
 * SponsorFlow - Company Relationship Deep-Dive View
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();
$pdo = getDBConnection();
$companyId = (int)($_GET['id'] ?? 0);

if ($companyId <= 0) {
    safeRedirect('companies.php');
}

// 1. Fetch Company Record
$stmt = $pdo->prepare("SELECT * FROM companies WHERE id = :id");
$stmt->execute([':id' => $companyId]);
$company = $stmt->fetch();

if (!$company) {
    $pageTitle = 'Company Not Found';
    include __DIR__ . '/includes/header.php';
    echo '<div class="alert error">Company not found or may have been deleted.</div>';
    echo '<p><a href="companies.php" class="btn sm">&larr; Back to Companies</a></p>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// 2. Fetch Related Contacts
$stmt = $pdo->prepare("SELECT * FROM contacts WHERE company_id = :cid ORDER BY id ASC");
$stmt->execute([':cid' => $companyId]);
$contacts = $stmt->fetchAll();

// 3. Fetch Related Outreach Records
$stmt = $pdo->prepare("
    SELECT o.*, c.name AS contact_name 
    FROM outreach o 
    LEFT JOIN contacts c ON o.contact_id = c.id 
    WHERE o.company_id = :cid 
    ORDER BY o.outreach_date DESC, o.id DESC
");
$stmt->execute([':cid' => $companyId]);
$outreachList = $stmt->fetchAll();

// 4. Fetch Related Follow-ups
$stmt = $pdo->prepare("
    SELECT f.*, c.name AS contact_name 
    FROM followups f 
    LEFT JOIN contacts c ON f.contact_id = c.id 
    WHERE f.company_id = :cid 
    ORDER BY f.followup_date ASC
");
$stmt->execute([':cid' => $companyId]);
$followupsList = $stmt->fetchAll();

// 5. Fetch Related Proposals
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS contact_name 
    FROM proposals p 
    LEFT JOIN contacts c ON p.contact_id = c.id 
    WHERE p.company_id = :cid 
    ORDER BY p.created_at DESC
");
$stmt->execute([':cid' => $companyId]);
$proposalsList = $stmt->fetchAll();

// 6. Fetch Related Sponsorships
$stmt = $pdo->prepare("SELECT * FROM sponsorships WHERE company_id = :cid ORDER BY start_date DESC");
$stmt->execute([':cid' => $companyId]);
$sponsorshipsList = $stmt->fetchAll();

// 7. Fetch Deliverables
$stmt = $pdo->prepare("
    SELECT d.*, s.package_name 
    FROM deliverables d 
    LEFT JOIN sponsorships s ON d.sponsorship_id = s.id 
    WHERE d.company_id = :cid 
    ORDER BY d.due_date ASC
");
$stmt->execute([':cid' => $companyId]);
$deliverablesList = $stmt->fetchAll();

// 8. Fetch Activity Timeline
$stmt = $pdo->prepare("
    SELECT * FROM activities 
    WHERE company_id = :cid 
    ORDER BY activity_date DESC, id DESC
");
$stmt->execute([':cid' => $companyId]);
$activitiesList = $stmt->fetchAll();

$pageTitle = $company['company_name'];
$activePage = 'companies';
include __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 1rem;">
    <a href="companies.php" class="btn sm ghost">&larr; Back to all companies</a>
</div>

<div class="head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <h1 style="margin: 0;"><?= e($company['company_name']) ?></h1>
        <?= statusBadge($company['status']) ?>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="contacts.php?company_id=<?= $companyId ?>#add" class="btn sm secondary">+ Add Contact</a>
        <a href="outreach.php?company_id=<?= $companyId ?>#add" class="btn sm secondary">+ Log Outreach</a>
        <a href="proposals.php?company_id=<?= $companyId ?>#add" class="btn sm">+ Create Proposal</a>
    </div>
</div>

<!-- Overview Cards Grid -->
<div class="cols2">
    <!-- Company Profile -->
    <section class="card">
        <h2>Company Profile</h2>
        <dl>
            <dt>Industry</dt>
            <dd><strong><?= e($company['industry']) ?></strong></dd>

            <dt>Location</dt>
            <dd><?= e($company['location']) ?></dd>

            <dt>Pipeline Status</dt>
            <dd><?= statusBadge($company['status']) ?></dd>

            <dt>Estimated Value</dt>
            <dd><strong style="color: var(--ok);"><?= formatCurrency($company['estimated_value']) ?></strong></dd>

            <dt>Website</dt>
            <dd>
                <?php if (!empty($company['website'])): ?>
                    <a href="http://<?= e($company['website']) ?>" target="_blank" rel="noopener"><?= e($company['website']) ?></a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </dd>

            <dt>Phone</dt>
            <dd><?= e($company['phone'] ?: '-') ?></dd>

            <dt>Email</dt>
            <dd><?= e($company['email'] ?: '-') ?></dd>

            <dt>Notes</dt>
            <dd><?= e($company['notes'] ?: '-') ?></dd>
        </dl>
    </section>

    <!-- Commercial Status -->
    <section class="card">
        <h2>Commercial Commitments</h2>
        <?php if (!empty($sponsorshipsList)): ?>
            <?php foreach ($sponsorshipsList as $sp): ?>
                <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--line);">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <strong><?= e($sp['package_name']) ?> (<?= e($sp['sponsorship_type']) ?>)</strong>
                        <span style="font-size: 1.1rem; font-weight: 700; color: var(--ok);"><?= formatCurrency($sp['amount']) ?></span>
                    </div>
                    <div style="margin-top: 0.4rem; display: flex; gap: 0.5rem; font-size: 0.85rem;">
                        Payment: <?= statusBadge($sp['payment_status']) ?>
                        Contract: <?= statusBadge($sp['contract_status']) ?>
                        Status: <?= statusBadge($sp['overall_status']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: var(--mute);">No active commercial contracts finalized yet.</p>
        <?php endif; ?>
    </section>
</div>

<!-- Contacts Section -->
<section class="card" style="margin-top: 1.25rem;">
    <h2>Key Contacts (<?= count($contacts) ?>)</h2>
    <?php if (!empty($contacts)): ?>
        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Preferred Channel</th>
                        <th>Relationship</th>
                        <th>Last Contacted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $cnt): ?>
                        <tr>
                            <td><strong><?= e($cnt['name']) ?></strong></td>
                            <td><?= e($cnt['designation']) ?></td>
                            <td><a href="mailto:<?= e($cnt['email']) ?>"><?= e($cnt['email']) ?></a></td>
                            <td><?= e($cnt['phone']) ?></td>
                            <td><?= e($cnt['preferred_contact']) ?></td>
                            <td><?= statusBadge($cnt['relationship_status']) ?></td>
                            <td><?= formatDate($cnt['last_contacted']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="color: var(--mute);">No contacts logged for this company yet.</p>
    <?php endif; ?>
</section>

<!-- Proposals Section -->
<section class="card" style="margin-top: 1.25rem;">
    <h2>Proposals (<?= count($proposalsList) ?>)</h2>
    <?php if (!empty($proposalsList)): ?>
        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th>Proposal #</th>
                        <th>Contact</th>
                        <th>Package</th>
                        <th>Amount</th>
                        <th>Sent Date</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proposalsList as $pr): ?>
                        <tr>
                            <td><strong><?= e($pr['proposal_number']) ?></strong></td>
                            <td><?= e($pr['contact_name'] ?: '-') ?></td>
                            <td><?= e($pr['package_name']) ?></td>
                            <td><strong><?= formatCurrency($pr['amount']) ?></strong></td>
                            <td><?= formatDate($pr['sent_date']) ?></td>
                            <td><?= formatDate($pr['valid_until']) ?></td>
                            <td><?= statusBadge($pr['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="color: var(--mute);">No proposals generated yet.</p>
    <?php endif; ?>
</section>

<!-- Outreach & Follow-ups Grid -->
<div class="cols2">
    <!-- Outreach History -->
    <section class="card">
        <h2>Outreach Log (<?= count($outreachList) ?>)</h2>
        <?php if (!empty($outreachList)): ?>
            <ul class="list">
                <?php foreach ($outreachList as $out): ?>
                    <li>
                        <div>
                            <strong><?= e($out['outreach_type']) ?>: <?= e($out['subject']) ?></strong>
                            <div style="font-size: 0.82rem; color: var(--mute);">
                                Sent to <?= e($out['contact_name'] ?: 'Contact') ?> on <?= formatDate($out['outreach_date']) ?> by <?= e($out['assigned_to']) ?>
                            </div>
                        </div>
                        <div><?= statusBadge($out['status']) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color: var(--mute);">No outreach records found.</p>
        <?php endif; ?>
    </section>

    <!-- Follow-ups -->
    <section class="card">
        <h2>Follow-ups (<?= count($followupsList) ?>)</h2>
        <?php if (!empty($followupsList)): ?>
            <ul class="list">
                <?php foreach ($followupsList as $fu): ?>
                    <li>
                        <div>
                            <strong><?= e($fu['reason']) ?></strong>
                            <div style="font-size: 0.82rem; color: var(--mute);">
                                Due: <?= formatDate($fu['followup_date']) ?> · Assigned to <?= e($fu['assigned_to']) ?>
                            </div>
                        </div>
                        <div><?= statusBadge($fu['status']) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color: var(--mute);">No follow-ups recorded.</p>
        <?php endif; ?>
    </section>
</div>

<!-- Deliverables Section -->
<section class="card" style="margin-top: 1.25rem;">
    <h2>Deliverables Promised (<?= count($deliverablesList) ?>)</h2>
    <?php if (!empty($deliverablesList)): ?>
        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th>Deliverable</th>
                        <th>Event</th>
                        <th>Due Date</th>
                        <th>Owner</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Completed Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deliverablesList as $del): ?>
                        <tr>
                            <td><strong><?= e($del['deliverable']) ?></strong></td>
                            <td><?= e($del['event_name']) ?></td>
                            <td><?= formatDate($del['due_date']) ?></td>
                            <td><?= e($del['owner']) ?></td>
                            <td><?= priorityBadge($del['priority']) ?></td>
                            <td><?= statusBadge($del['status']) ?></td>
                            <td><?= formatDate($del['completion_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="color: var(--mute);">No deliverables registered yet.</p>
    <?php endif; ?>
</section>

<!-- Activity Timeline -->
<section class="card" style="margin-top: 1.25rem;">
    <h2>Activity Timeline</h2>
    <?php if (!empty($activitiesList)): ?>
        <ul class="timeline" style="margin-top: 1rem;">
            <?php foreach ($activitiesList as $act): ?>
                <li>
                    <strong><?= e($act['activity_type']) ?></strong>: <?= e($act['description']) ?>
                    <small><?= formatDate($act['activity_date']) ?> · Logged by <?= e($act['created_by']) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p style="color: var(--mute);">No activity history.</p>
    <?php endif; ?>
</section>

<?php
include __DIR__ . '/includes/footer.php';
?>
