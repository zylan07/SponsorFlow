<?php
/**
 * SponsorFlow - Database-Driven Dashboard
 */
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/header.php';

try {
    $pdo = getDBConnection();

    // 1. Core KPIs
    $totalCompanies = (int)$pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn();
    $activeProspects = (int)$pdo->query("SELECT COUNT(*) FROM companies WHERE status IN ('Prospect', 'Contacted')")->fetchColumn();
    $proposalsSent = (int)$pdo->query("SELECT COUNT(*) FROM proposals WHERE status != 'Draft'")->fetchColumn();
    $confirmedSponsors = (int)$pdo->query("SELECT COUNT(*) FROM companies WHERE status = 'Confirmed'")->fetchColumn();

    // Financial KPIs
    $pipelineValue = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM proposals WHERE status IN ('Draft', 'Sent', 'Viewed', 'Negotiation')")->fetchColumn();
    $confirmedValue = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM sponsorships WHERE overall_status != 'Cancelled'")->fetchColumn();
    
    // Paid amount estimation from payments
    $paidAmount = (float)$pdo->query("
        SELECT COALESCE(SUM(
            CASE 
                WHEN payment_status = 'Paid' THEN amount 
                WHEN payment_status = 'Partial' THEN (amount * 0.5) 
                ELSE 0 
            END
        ), 0) FROM sponsorships WHERE overall_status != 'Cancelled'
    ")->fetchColumn();
    $outstandingAmount = max(0, $confirmedValue - $paidAmount);

    // Follow-ups & Deliverables KPIs
    $pendingFollowups = (int)$pdo->query("SELECT COUNT(*) FROM followups WHERE status IN ('Pending', 'Overdue')")->fetchColumn();
    $overdueFollowups = (int)$pdo->query("SELECT COUNT(*) FROM followups WHERE status = 'Pending' AND followup_date < CURRENT_DATE()")->fetchColumn();
    
    $totalDeliverables = (int)$pdo->query("SELECT COUNT(*) FROM deliverables")->fetchColumn();
    $completedDeliverables = (int)$pdo->query("SELECT COUNT(*) FROM deliverables WHERE status = 'Completed'")->fetchColumn();
    $pendingDeliverables = $totalDeliverables - $completedDeliverables;
    $deliverablePct = $totalDeliverables > 0 ? round(($completedDeliverables / $totalDeliverables) * 100) : 0;

    // 2. Pipeline Breakdown by Status
    $statusOrder = ['Prospect', 'Contacted', 'Proposal Sent', 'Interested', 'Negotiation', 'Confirmed', 'Rejected'];
    $stmt = $pdo->query("SELECT status, COUNT(*) AS count FROM companies GROUP BY status");
    $dbPipeline = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $pipelineCounts = [];
    $maxPipelineCount = 1;
    foreach ($statusOrder as $st) {
        $count = $dbPipeline[$st] ?? 0;
        $pipelineCounts[$st] = $count;
        if ($count > $maxPipelineCount) {
            $maxPipelineCount = $count;
        }
    }

    // 3. Upcoming Follow-ups (nearest 5)
    $stmt = $pdo->query("
        SELECT f.*, c.company_name 
        FROM followups f 
        JOIN companies c ON f.company_id = c.id 
        WHERE f.status != 'Completed' 
        ORDER BY f.followup_date ASC 
        LIMIT 5
    ");
    $upcomingFollowups = $stmt->fetchAll();

    // 4. Recent Activities (latest 8)
    $stmt = $pdo->query("
        SELECT a.*, c.company_name 
        FROM activities a 
        JOIN companies c ON a.company_id = c.id 
        ORDER BY a.activity_date DESC, a.id DESC 
        LIMIT 8
    ");
    $recentActivities = $stmt->fetchAll();

    // 5. Deliverable Breakdown
    $stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM deliverables GROUP BY status");
    $deliverableStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    echo '<div class="alert error">Failed to load dashboard metrics: ' . e($e->getMessage()) . '</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
?>

<div class="head" style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1rem;">
    <div>
        <p class="lead" style="margin: 0; color: var(--mute);">
            Real-time sponsorship metrics from MySQL database. 
            <?php if ($overdueFollowups > 0): ?>
                <strong style="color: var(--bad);"><?= $overdueFollowups ?> follow-up<?= $overdueFollowups === 1 ? '' : 's' ?> overdue.</strong>
            <?php else: ?>
                All follow-ups are up to date.
            <?php endif; ?>
        </p>
    </div>
    <div>
        <a href="companies.php?action=new" class="btn sm">+ Add Company</a>
    </div>
</div>

<!-- Primary KPI Cards Grid -->
<section class="stats">
    <div class="card stat">
        <b><?= $totalCompanies ?></b>
        <span>Total Companies</span>
    </div>
    <div class="card stat">
        <b><?= $activeProspects ?></b>
        <span>Active Prospects</span>
    </div>
    <div class="card stat">
        <b><?= $proposalsSent ?></b>
        <span>Proposals Sent</span>
    </div>
    <div class="card stat">
        <b style="color: var(--ok);"><?= $confirmedSponsors ?></b>
        <span>Confirmed Sponsors</span>
    </div>
    <div class="card stat">
        <b><?= formatCurrency($pipelineValue) ?></b>
        <span>Pipeline Value</span>
    </div>
    <div class="card stat">
        <b style="color: var(--ok);"><?= formatCurrency($confirmedValue) ?></b>
        <span>Confirmed Value</span>
    </div>
    <div class="card stat">
        <b><?= $pendingFollowups ?></b>
        <span>Pending Follow-ups</span>
    </div>
    <div class="card stat">
        <b><?= $pendingDeliverables ?></b>
        <span>Pending Deliverables</span>
    </div>
</section>

<!-- Middle Visual Grid -->
<div class="cols2">
    <!-- Sponsorship Pipeline Card -->
    <section class="card sect">
        <h2>Sponsorship Pipeline (Companies)</h2>
        <div class="bars">
            <?php foreach ($pipelineCounts as $stage => $count): ?>
                <?php $widthPct = round(($count / $maxPipelineCount) * 100); ?>
                <div class="row">
                    <span><?= e($stage) ?></span>
                    <i style="width: <?= $widthPct ?>%;"></i>
                    <b><?= $count ?></b>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Financial Performance Card -->
    <section class="card sect">
        <h2>Financial Performance Summary</h2>
        <dl style="gap: 0.75rem 1rem; margin-top: 0.5rem;">
            <dt>Active Pipeline</dt>
            <dd><strong><?= formatCurrency($pipelineValue) ?></strong></dd>

            <dt>Confirmed Value</dt>
            <dd><strong style="color: var(--ok);"><?= formatCurrency($confirmedValue) ?></strong></dd>

            <dt>Amount Received</dt>
            <dd><strong style="color: var(--pri);"><?= formatCurrency($paidAmount) ?></strong></dd>

            <dt>Outstanding Balance</dt>
            <dd><strong style="color: var(--warn);"><?= formatCurrency($outstandingAmount) ?></strong></dd>
        </dl>
        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--line);">
            <small style="color: var(--mute);">Based on signed sponsorships and active package proposals.</small>
        </div>
    </section>

    <!-- Deliverables Progress Card -->
    <section class="card sect">
        <h2>Deliverables Fulfillment Progress</h2>
        <div style="margin: 0.5rem 0 1rem 0;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.95rem;">
                <strong><?= $deliverablePct ?>% Completed</strong>
                <span style="color: var(--mute);"><?= $completedDeliverables ?> of <?= $totalDeliverables ?> items</span>
            </div>
            <span class="prog"><i style="width: <?= $deliverablePct ?>%;"></i></span>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.88rem; color: var(--mute); margin-top: 1rem;">
            <div>Completed: <strong style="color: var(--ok);"><?= $deliverableStats['Completed'] ?? 0 ?></strong></div>
            <div>In Progress: <strong style="color: var(--pri);"><?= $deliverableStats['In Progress'] ?? 0 ?></strong></div>
            <div>Delayed: <strong style="color: var(--bad);"><?= $deliverableStats['Delayed'] ?? 0 ?></strong></div>
            <div>Not Started: <strong><?= $deliverableStats['Not Started'] ?? 0 ?></strong></div>
        </div>
    </section>

    <!-- Upcoming Follow-ups Card -->
    <section class="card sect">
        <h2>Upcoming & Priority Follow-ups</h2>
        <?php if (!empty($upcomingFollowups)): ?>
            <ul class="list">
                <?php foreach ($upcomingFollowups as $fu): ?>
                    <?php 
                        $isLate = ($fu['followup_date'] < date('Y-m-d'));
                        $isToday = ($fu['followup_date'] === date('Y-m-d'));
                        $statusText = $isLate ? 'Overdue' : ($isToday ? 'Today' : 'Upcoming');
                    ?>
                    <li>
                        <div>
                            <strong><a href="company-details.php?id=<?= $fu['company_id'] ?>"><?= e($fu['company_name']) ?></a></strong>
                            <div style="font-size: 0.85rem; color: var(--mute);"><?= e($fu['reason']) ?></div>
                        </div>
                        <div style="text-align: right; white-space: nowrap;">
                            <?= statusBadge($statusText) ?>
                            <small style="display: block; margin-top: 2px;"><?= formatDate($fu['followup_date'], 'd M') ?></small>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color: var(--mute);">No upcoming follow-ups scheduled.</p>
        <?php endif; ?>
    </section>
</div>

<!-- Recent Activity Timeline Card -->
<section class="card sect" style="margin-top: 1.25rem;">
    <h2>Recent Sponsorship Activity</h2>
    <?php if (!empty($recentActivities)): ?>
        <ul class="timeline" style="margin-top: 1rem;">
            <?php foreach ($recentActivities as $act): ?>
                <li>
                    <strong><a href="company-details.php?id=<?= $act['company_id'] ?>"><?= e($act['company_name']) ?></a></strong>: 
                    <?= e($act['description']) ?>
                    <small>Logged on <?= formatDate($act['activity_date']) ?> by <?= e($act['created_by']) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p style="color: var(--mute);">No recent activity recorded.</p>
    <?php endif; ?>
</section>

<?php
include __DIR__ . '/includes/footer.php';
?>
