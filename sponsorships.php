<?php
/**
 * SponsorFlow - Sponsorships & Commercial Contracts Module (CRUD)
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();
$pdo = getDBConnection();
$currentUser = currentUser();

$TYPES = [
    'Title Sponsor',
    'Gold Sponsor',
    'Silver Sponsor',
    'Bronze Sponsor',
    'Technology Partner',
    'Media Partner',
    'Community Partner',
    'Internship Partner',
    'Industry Partner'
];

$PAYMENT_STATUSES = ['Pending', 'Partial', 'Paid', 'Overdue'];
$CONTRACT_STATUSES = ['Not Started', 'Draft', 'Sent', 'Signed'];
$OVERALL_STATUSES = ['Active', 'Completed', 'On Hold', 'Cancelled'];

$allCompanies = $pdo->query("SELECT id, company_name FROM companies ORDER BY company_name ASC")->fetchAll();

// Handle POST actions: create, update, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $companyId = (int)($_POST['company_id'] ?? 0);
        $packageName = trim($_POST['package_name'] ?? '');
        $sponsorshipType = trim($_POST['sponsorship_type'] ?? 'Gold Sponsor');
        $amount = (float)($_POST['amount'] ?? 0);
        $startDate = trim($_POST['start_date'] ?? date('Y-m-d'));
        $endDate = trim($_POST['end_date'] ?? date('Y-m-d', strtotime('+90 days')));
        $paymentStatus = trim($_POST['payment_status'] ?? 'Pending');
        $contractStatus = trim($_POST['contract_status'] ?? 'Draft');
        $overallStatus = trim($_POST['overall_status'] ?? 'Active');

        if ($companyId <= 0 || empty($packageName) || empty($startDate) || empty($endDate)) {
            setFlashMessage('error', 'Company, package name, start date, and end date are required.');
        } elseif ($endDate < $startDate) {
            setFlashMessage('error', 'End date must be on or after start date.');
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $pdo->prepare("
                        INSERT INTO sponsorships (company_id, package_name, sponsorship_type, amount, start_date, end_date, payment_status, contract_status, overall_status)
                        VALUES (:cid, :pkg, :type, :amt, :sdate, :edate, :pay, :contract, :overall)
                    ");
                    $stmt->execute([
                        ':cid'      => $companyId,
                        ':pkg'      => $packageName,
                        ':type'     => $sponsorshipType,
                        ':amt'      => $amount,
                        ':sdate'    => $startDate,
                        ':edate'    => $endDate,
                        ':pay'      => $paymentStatus,
                        ':contract' => $contractStatus,
                        ':overall'  => $overallStatus
                    ]);
                    $newId = (int)$pdo->lastInsertId();

                    // Ensure company status is Confirmed
                    $uStmt = $pdo->prepare("UPDATE companies SET status = 'Confirmed', estimated_value = :val WHERE id = :cid");
                    $uStmt->execute([':val' => $amount, ':cid' => $companyId]);

                    logActivity($pdo, $companyId, 'Sponsorship Agreement', "Sponsorship agreement #{$newId} recorded ({$sponsorshipType}, " . formatCurrency($amount) . ").", $currentUser['name']);
                    setFlashMessage('success', "Sponsorship commitment recorded successfully.");
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE sponsorships 
                        SET company_id = :cid, package_name = :pkg, sponsorship_type = :type, 
                            amount = :amt, start_date = :sdate, end_date = :edate, 
                            payment_status = :pay, contract_status = :contract, overall_status = :overall
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':cid'      => $companyId,
                        ':pkg'      => $packageName,
                        ':type'     => $sponsorshipType,
                        ':amt'      => $amount,
                        ':sdate'    => $startDate,
                        ':edate'    => $endDate,
                        ':pay'      => $paymentStatus,
                        ':contract' => $contractStatus,
                        ':overall'  => $overallStatus,
                        ':id'       => $id
                    ]);
                    logActivity($pdo, $companyId, 'Sponsorship Updated', "Sponsorship #{$id} updated. Payment: {$paymentStatus}, Contract: {$contractStatus}.", $currentUser['name']);
                    setFlashMessage('success', "Sponsorship #{$id} updated.");
                }
            } catch (PDOException $e) {
                setFlashMessage('error', 'Database error: ' . $e->getMessage());
            }
        }
        safeRedirect('sponsorships.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM sponsorships WHERE id = :id");
                $stmt->execute([':id' => $id]);
                setFlashMessage('success', 'Sponsorship deleted.');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to delete sponsorship: ' . $e->getMessage());
            }
        }
        safeRedirect('sponsorships.php');
    }
}

// Filters
$search = trim($_GET['q'] ?? '');
$companyFilter = (int)($_GET['company_id'] ?? 0);
$typeFilter = trim($_GET['type'] ?? '');
$paymentFilter = trim($_GET['payment'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$sql = "
    SELECT s.*, c.company_name,
           (SELECT COUNT(*) FROM deliverables WHERE sponsorship_id = s.id) AS total_deliv,
           (SELECT COUNT(*) FROM deliverables WHERE sponsorship_id = s.id AND status = 'Completed') AS completed_deliv
    FROM sponsorships s 
    JOIN companies c ON s.company_id = c.id 
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (s.package_name LIKE :s1 OR c.company_name LIKE :s2)";
    $term = "%{$search}%";
    $params[':s1'] = $term;
    $params[':s2'] = $term;
}

if ($companyFilter > 0) {
    $sql .= " AND s.company_id = :cid";
    $params[':cid'] = $companyFilter;
}

if ($typeFilter !== '') {
    $sql .= " AND s.sponsorship_type = :type";
    $params[':type'] = $typeFilter;
}

if ($paymentFilter !== '') {
    $sql .= " AND s.payment_status = :pay";
    $params[':pay'] = $paymentFilter;
}

if ($statusFilter !== '') {
    $sql .= " AND s.overall_status = :status";
    $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY s.amount DESC, s.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sponsorshipsList = $stmt->fetchAll();

// Calculate totals from filtered records
$totalCommitment = 0;
$totalPaid = 0;
foreach ($sponsorshipsList as $sp) {
    $totalCommitment += (float)$sp['amount'];
    if ($sp['payment_status'] === 'Paid') {
        $totalPaid += (float)$sp['amount'];
    } elseif ($sp['payment_status'] === 'Partial') {
        $totalPaid += (float)($sp['amount'] * 0.5);
    }
}
$outstanding = max(0, $totalCommitment - $totalPaid);

$pageTitle = 'Sponsorships';
$activePage = 'sponsorships';
include __DIR__ . '/includes/header.php';
?>

<div class="head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div>
        <p class="lead" style="margin: 0; color: var(--mute);">
            Commercial agreements, payment status, and contract governance.
        </p>
    </div>
    <div>
        <button class="btn" onclick="openAddSponsorshipModal()">+ Add Sponsorship</button>
    </div>
</div>

<!-- Financial Summary Bar -->
<section class="stats" style="margin-bottom: 1.25rem;">
    <div class="card stat">
        <b style="color: var(--ok);"><?= formatCurrency($totalCommitment) ?></b>
        <span>Total Committed (<?= count($sponsorshipsList) ?>)</span>
    </div>
    <div class="card stat">
        <b style="color: var(--pri);"><?= formatCurrency($totalPaid) ?></b>
        <span>Collected Payments</span>
    </div>
    <div class="card stat">
        <b style="color: var(--warn);"><?= formatCurrency($outstanding) ?></b>
        <span>Outstanding Balance</span>
    </div>
</section>

<!-- Filters Bar -->
<form method="GET" action="sponsorships.php" class="tools">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search sponsor or package name" aria-label="Search">
    
    <select name="company_id" aria-label="Filter by Company" onchange="this.form.submit()">
        <option value="">All Sponsors</option>
        <?php foreach ($allCompanies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= ($companyFilter === (int)$comp['id']) ? 'selected' : '' ?>>
                <?= e($comp['company_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="type" aria-label="Filter by Type" onchange="this.form.submit()">
        <option value="">All Types</option>
        <?php foreach ($TYPES as $t): ?>
            <option value="<?= e($t) ?>" <?= ($typeFilter === $t) ? 'selected' : '' ?>><?= e($t) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="payment" aria-label="Filter by Payment" onchange="this.form.submit()">
        <option value="">All Payments</option>
        <?php foreach ($PAYMENT_STATUSES as $p): ?>
            <option value="<?= e($p) ?>" <?= ($paymentFilter === $p) ? 'selected' : '' ?>><?= e($p) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="status" aria-label="Filter by Overall Status" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <?php foreach ($OVERALL_STATUSES as $os): ?>
            <option value="<?= e($os) ?>" <?= ($statusFilter === $os) ? 'selected' : '' ?>><?= e($os) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn sm secondary">Filter</button>
    <?php if ($search !== '' || $companyFilter > 0 || $typeFilter !== '' || $paymentFilter !== '' || $statusFilter !== ''): ?>
        <a href="sponsorships.php" class="btn sm ghost">Clear</a>
    <?php endif; ?>
</form>

<!-- Sponsorships Table -->
<div class="tw">
    <table>
        <thead>
            <tr>
                <th>Sponsor</th>
                <th>Package Name</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Payment</th>
                <th>Contract</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($sponsorshipsList)): ?>
                <?php foreach ($sponsorshipsList as $sp): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="company-details.php?id=<?= $sp['company_id'] ?>">
                                    <?= e($sp['company_name']) ?>
                                </a>
                            </strong>
                            <?php if ($sp['total_deliv'] > 0): ?>
                                <small style="display: block; color: var(--mute);">
                                    <?= $sp['completed_deliv'] ?> of <?= $sp['total_deliv'] ?> deliverables completed
                                </small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($sp['package_name']) ?></td>
                        <td><span class="badge"><?= e($sp['sponsorship_type']) ?></span></td>
                        <td><strong><?= formatCurrency($sp['amount']) ?></strong></td>
                        <td><?= formatDate($sp['start_date']) ?></td>
                        <td><?= formatDate($sp['end_date']) ?></td>
                        <td><?= statusBadge($sp['payment_status']) ?></td>
                        <td><?= statusBadge($sp['contract_status']) ?></td>
                        <td><?= statusBadge($sp['overall_status']) ?></td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button type="button" class="btn sm ghost" onclick='editSponsorship(<?= json_encode($sp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Edit</button>
                            <form method="POST" action="sponsorships.php" style="display: inline;" onsubmit="return confirmDelete('Are you sure you want to delete this sponsorship record?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $sp['id'] ?>">
                                <button type="submit" class="btn sm danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" style="text-align: center; padding: 2.5rem; color: var(--mute);">
                        No commercial sponsorships found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Add / Edit Sponsorship -->
<dialog id="sponsorshipDialog">
    <form method="POST" action="sponsorships.php" id="sponsorshipForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="sponsorshipFormAction" value="create">
        <input type="hidden" name="id" id="sponsorshipId" value="">

        <h2 id="sponsorshipModalTitle">Record Sponsorship Commitment</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="sp_company">Sponsor Company *</label>
                <select id="sp_company" name="company_id" required>
                    <option value="">-- Select Company --</option>
                    <?php foreach ($allCompanies as $comp): ?>
                        <option value="<?= $comp['id'] ?>"><?= e($comp['company_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="sp_pkg">Package Name *</label>
                <input id="sp_pkg" name="package_name" required placeholder="e.g. Gold Sponsor Package">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="sp_type">Sponsorship Type *</label>
                <select id="sp_type" name="sponsorship_type" required>
                    <?php foreach ($TYPES as $t): ?>
                        <option value="<?= e($t) ?>"><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="sp_amount">Committed Amount (₹) *</label>
                <input id="sp_amount" name="amount" type="number" min="0" step="1000" required placeholder="50000">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="sp_start">Agreement Start Date *</label>
                <input id="sp_start" name="start_date" type="date" required value="<?= date('Y-m-d') ?>">
            </div>
            <div>
                <label for="sp_end">Agreement End Date *</label>
                <input id="sp_end" name="end_date" type="date" required value="<?= date('Y-m-d', strtotime('+90 days')) ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="sp_pay">Payment Status</label>
                <select id="sp_pay" name="payment_status">
                    <?php foreach ($PAYMENT_STATUSES as $p): ?>
                        <option value="<?= e($p) ?>"><?= e($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="sp_contract">Contract Status</label>
                <select id="sp_contract" name="contract_status">
                    <?php foreach ($CONTRACT_STATUSES as $c): ?>
                        <option value="<?= e($c) ?>"><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="sp_overall">Overall Status</label>
                <select id="sp_overall" name="overall_status">
                    <?php foreach ($OVERALL_STATUSES as $o): ?>
                        <option value="<?= e($o) ?>"><?= e($o) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <p style="margin-top: 1.5rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button type="button" class="btn ghost" onclick="closeModal('sponsorshipDialog')">Cancel</button>
            <button type="submit" class="btn">Save Sponsorship</button>
        </p>
    </form>
</dialog>

<script>
function openAddSponsorshipModal() {
    $('sponsorshipForm').reset();
    $('sponsorshipFormAction').value = 'create';
    $('sponsorshipId').value = '';
    $('sponsorshipModalTitle').textContent = 'Record Sponsorship Commitment';
    $('sp_start').value = new Date().toISOString().slice(0, 10);
    
    const d = new Date();
    d.setDate(d.getDate() + 90);
    $('sp_end').value = d.toISOString().slice(0, 10);
    
    const urlParams = new URLSearchParams(window.location.search);
    const cid = urlParams.get('company_id');
    if (cid) {
        $('sp_company').value = cid;
    }
    
    openModal('sponsorshipDialog');
}

function editSponsorship(sp) {
    $('sponsorshipForm').reset();
    $('sponsorshipFormAction').value = 'update';
    $('sponsorshipId').value = sp.id;
    $('sponsorshipModalTitle').textContent = 'Edit Sponsorship #' + sp.id;
    
    $('sp_company').value = sp.company_id || '';
    $('sp_pkg').value = sp.package_name || '';
    $('sp_type').value = sp.sponsorship_type || 'Gold Sponsor';
    $('sp_amount').value = sp.amount || 0;
    $('sp_start').value = sp.start_date || '';
    $('sp_end').value = sp.end_date || '';
    $('sp_pay').value = sp.payment_status || 'Pending';
    $('sp_contract').value = sp.contract_status || 'Draft';
    $('sp_overall').value = sp.overall_status || 'Active';
    
    openModal('sponsorshipDialog');
}

window.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash === '#add') {
        openAddSponsorshipModal();
    }
});
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>
