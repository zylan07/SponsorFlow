<?php
/**
 * SponsorFlow - Analytics & Executive Reports
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();
$pdo = getDBConnection();

$pageTitle = 'Executive Reports & Analytics';
$activePage = 'reports';

try {
    // 1. Pipeline Breakdown
    $statusOrder = ['Prospect', 'Contacted', 'Proposal Sent', 'Interested', 'Negotiation', 'Confirmed', 'Rejected'];
    $stmt = $pdo->query("SELECT status, COUNT(*) AS cnt, COALESCE(SUM(estimated_value), 0) AS val FROM companies GROUP BY status");
    $stageData = [];
    foreach ($stmt->fetchAll() as $row) {
        $stageData[$row['status']] = ['cnt' => (int)$row['cnt'], 'val' => (float)$row['val']];
    }

    $maxCompanyCount = 1;
    $maxStageValue = 1;
    foreach ($statusOrder as $st) {
        $cnt = $stageData[$st]['cnt'] ?? 0;
        $val = $stageData[$st]['val'] ?? 0;
        if ($cnt > $maxCompanyCount) $maxCompanyCount = $cnt;
        if ($val > $maxStageValue) $maxStageValue = $val;
    }

    // 2. Financial Metrics
    $openProposalValue = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM proposals WHERE status IN ('Draft', 'Sent', 'Viewed', 'Negotiation')")->fetchColumn();
    $confirmedValue = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM sponsorships WHERE overall_status != 'Cancelled'")->fetchColumn();
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
    $unpaidSponsorshipsCount = (int)$pdo->query("SELECT COUNT(*) FROM sponsorships WHERE payment_status != 'Paid' AND overall_status != 'Cancelled'")->fetchColumn();

    // 3. Outreach Performance
    $totalOutreach = (int)$pdo->query("SELECT COUNT(*) FROM outreach")->fetchColumn();
    $respondedOutreach = (int)$pdo->query("SELECT COUNT(*) FROM outreach WHERE status IN ('Responded', 'Interested')")->fetchColumn();
    $interestedOutreach = (int)$pdo->query("SELECT COUNT(*) FROM outreach WHERE status = 'Interested'")->fetchColumn();
    $noResponseOutreach = (int)$pdo->query("SELECT COUNT(*) FROM outreach WHERE status = 'No Response'")->fetchColumn();
    $responseRate = $totalOutreach > 0 ? round(($respondedOutreach / $totalOutreach) * 100) : 0;

    $contactedCompanies = (int)$pdo->query("SELECT COUNT(*) FROM companies WHERE status != 'Prospect'")->fetchColumn();
    $confirmedCompanies = (int)$pdo->query("SELECT COUNT(*) FROM companies WHERE status = 'Confirmed'")->fetchColumn();
    $conversionRate = $contactedCompanies > 0 ? round(($confirmedCompanies / $contactedCompanies) * 100) : 0;

    // 4. Deliverable Metrics
    $totalDeliverables = (int)$pdo->query("SELECT COUNT(*) FROM deliverables")->fetchColumn();
    $completedDeliverables = (int)$pdo->query("SELECT COUNT(*) FROM deliverables WHERE status = 'Completed'")->fetchColumn();
    $inProgressDeliverables = (int)$pdo->query("SELECT COUNT(*) FROM deliverables WHERE status = 'In Progress'")->fetchColumn();
    $delayedDeliverables = (int)$pdo->query("SELECT COUNT(*) FROM deliverables WHERE status = 'Delayed'")->fetchColumn();
    $delivCompletionRate = $totalDeliverables > 0 ? round(($completedDeliverables / $totalDeliverables) * 100) : 0;

    // 5. Distributions
    $industryDist = $pdo->query("SELECT industry, COUNT(*) AS cnt FROM companies WHERE status = 'Confirmed' GROUP BY industry ORDER BY cnt DESC")->fetchAll(PDO::FETCH_KEY_PAIR);
    $typeDist = $pdo->query("SELECT sponsorship_type, COUNT(*) AS cnt FROM sponsorships GROUP BY sponsorship_type ORDER BY cnt DESC")->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    include __DIR__ . '/includes/header.php';
    echo '<div class="alert error">Failed to load reports: ' . e($e->getMessage()) . '</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="head" style="margin-bottom: 1rem;">
    <p class="lead" style="margin: 0; color: var(--mute);">
        Consolidated analytics, conversion metrics, and distribution charts calculated directly from MySQL.
    </p>
</div>

<!-- Primary Financial Analytics Card -->
<section class="card sect">
    <h2>Financial Overview & Cash Flow</h2>
    <div class="stats" style="margin-top: 0.5rem;">
        <div class="card stat">
            <b><?= formatCurrency($openProposalValue) ?></b>
            <span>Open Proposal Pipeline</span>
        </div>
        <div class="card stat">
            <b style="color: var(--ok);"><?= formatCurrency($confirmedValue) ?></b>
            <span>Confirmed Commitments</span>
        </div>
        <div class="card stat">
            <b style="color: var(--pri);"><?= formatCurrency($paidAmount) ?></b>
            <span>Cash Collected</span>
        </div>
        <div class="card stat">
            <b style="color: var(--warn);"><?= formatCurrency($outstandingAmount) ?></b>
            <span>Receivables Pending</span>
        </div>
        <div class="card stat">
            <b><?= $unpaidSponsorshipsCount ?></b>
            <span>Sponsorships with Pending Dues</span>
        </div>
    </div>
</section>

<!-- Pipeline Visualization Grid -->
<div class="cols2">
    <!-- Pipeline by Company Counts -->
    <section class="card sect">
        <h2>Sponsorship Pipeline (Volume)</h2>
        <div class="bars">
            <?php foreach ($statusOrder as $st): ?>
                <?php 
                    $c = $stageData[$st]['cnt'] ?? 0;
                    $w = round(($c / $maxCompanyCount) * 100);
                ?>
                <div class="row">
                    <span><?= e($st) ?></span>
                    <i style="width: <?= $w ?>%;"></i>
                    <b><?= $c ?></b>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Pipeline by Monetary Value -->
    <section class="card sect">
        <h2>Pipeline Value by Stage</h2>
        <div class="bars">
            <?php foreach ($statusOrder as $st): ?>
                <?php 
                    $v = $stageData[$st]['val'] ?? 0;
                    $w = round(($v / $maxStageValue) * 100);
                ?>
                <div class="row">
                    <span><?= e($st) ?></span>
                    <i style="width: <?= $w ?>%;"></i>
                    <b style="font-size: 0.82rem;"><?= formatCurrency($v) ?></b>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<!-- Performance Metrics Grid -->
<div class="cols2">
    <!-- Outreach & Conversion Card -->
    <section class="card sect">
        <h2>Outreach & Conversion Performance</h2>
        <div class="stats" style="margin-top: 0.5rem; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));">
            <div class="card stat">
                <b><?= $totalOutreach ?></b>
                <span>Total Touches</span>
            </div>
            <div class="card stat">
                <b style="color: var(--ok);"><?= $responseRate ?>%</b>
                <span>Response Rate</span>
            </div>
            <div class="card stat">
                <b style="color: var(--pri);"><?= $conversionRate ?>%</b>
                <span>Conversion Rate</span>
            </div>
        </div>
        <dl style="margin-top: 1rem; border-top: 1px solid var(--line); padding-top: 0.75rem;">
            <dt>Positive Responses</dt>
            <dd><?= $respondedOutreach ?> interactions</dd>

            <dt>Expressed Interest</dt>
            <dd><strong style="color: var(--ok);"><?= $interestedOutreach ?> companies</strong></dd>

            <dt>Unanswered Outreach</dt>
            <dd style="color: var(--mute);"><?= $noResponseOutreach ?> touches</dd>

            <dt>Conversion Ratio</dt>
            <dd><small style="color: var(--mute);">Confirmed sponsors (<?= $confirmedCompanies ?>) &divide; Contacted leads (<?= $contactedCompanies ?>)</small></dd>
        </dl>
    </section>

    <!-- Deliverable Execution Card -->
    <section class="card sect">
        <h2>Deliverables Fulfillment Efficiency</h2>
        <div style="margin: 0.5rem 0 1rem 0;">
            <div style="display: flex; justify-content: space-between; font-size: 0.95rem; margin-bottom: 0.5rem;">
                <strong><?= $delivCompletionRate ?>% Fulfilled</strong>
                <span style="color: var(--mute);"><?= $completedDeliverables ?> of <?= $totalDeliverables ?> tasks</span>
            </div>
            <span class="prog"><i style="width: <?= $delivCompletionRate ?>%;"></i></span>
        </div>
        <div class="stats" style="margin-top: 1rem; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));">
            <div class="card stat">
                <b style="color: var(--ok);"><?= $completedDeliverables ?></b>
                <span>Completed</span>
            </div>
            <div class="card stat">
                <b style="color: var(--pri);"><?= $inProgressDeliverables ?></b>
                <span>In Progress</span>
            </div>
            <div class="card stat">
                <b style="color: var(--bad);"><?= $delayedDeliverables ?></b>
                <span>Delayed</span>
            </div>
        </div>
    </section>
</div>

<!-- Breakdown by Category Grid -->
<div class="cols2">
    <!-- Confirmed Sponsors by Industry -->
    <section class="card sect">
        <h2>Confirmed Sponsors by Industry</h2>
        <?php if (!empty($industryDist)): ?>
            <?php $maxInd = max(1, ...array_values($industryDist)); ?>
            <div class="bars">
                <?php foreach ($industryDist as $ind => $cnt): ?>
                    <div class="row">
                        <span><?= e($ind) ?></span>
                        <i style="width: <?= round(($cnt / $maxInd) * 100) ?>%;"></i>
                        <b><?= $cnt ?></b>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: var(--mute);">No confirmed sponsors yet.</p>
        <?php endif; ?>
    </section>

    <!-- Sponsorships by Package Tier -->
    <section class="card sect">
        <h2>Sponsorships by Tier / Type</h2>
        <?php if (!empty($typeDist)): ?>
            <?php $maxT = max(1, ...array_values($typeDist)); ?>
            <div class="bars">
                <?php foreach ($typeDist as $t => $cnt): ?>
                    <div class="row">
                        <span><?= e($t) ?></span>
                        <i style="width: <?= round(($cnt / $maxT) * 100) ?>%;"></i>
                        <b><?= $cnt ?></b>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: var(--mute);">No sponsorships yet.</p>
        <?php endif; ?>
    </section>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>
