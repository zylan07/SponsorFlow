<?php
/**
 * SponsorFlow - Proposals Module (CRUD & Sponsorship Conversion)
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();
$pdo = getDBConnection();
$currentUser = currentUser();

$PACKAGES = [
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

$STATUSES = ['Draft', 'Sent', 'Viewed', 'Negotiation', 'Accepted', 'Rejected', 'Expired'];

$allCompanies = $pdo->query("SELECT id, company_name FROM companies ORDER BY company_name ASC")->fetchAll();
$allContacts = $pdo->query("SELECT id, company_id, name FROM contacts ORDER BY name ASC")->fetchAll();

// Handle POST actions: create, update, delete, convert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
    $action = $_POST['action'] ?? '';

    // Convert Accepted proposal into a confirmed Sponsorship
    if ($action === 'convert') {
        $proposalId = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM proposals WHERE id = :id");
        $stmt->execute([':id' => $proposalId]);
        $prop = $stmt->fetch();

        if ($prop) {
            // Check if already converted
            $checkStmt = $pdo->prepare("SELECT id FROM sponsorships WHERE proposal_id = :pid");
            $checkStmt->execute([':pid' => $proposalId]);
            if ($checkStmt->fetch()) {
                setFlashMessage('warn', 'This proposal has already been converted into a sponsorship.');
            } else {
                try {
                    $pdo->beginTransaction();

                    // 1. Create sponsorship
                    $insStmt = $pdo->prepare("
                        INSERT INTO sponsorships (company_id, proposal_id, package_name, sponsorship_type, amount, start_date, end_date, payment_status, contract_status, overall_status)
                        VALUES (:cid, :pid, :pkg, :stype, :amt, :sdate, :edate, 'Pending', 'Draft', 'Active')
                    ");
                    $insStmt->execute([
                        ':cid'   => $prop['company_id'],
                        ':pid'   => $proposalId,
                        ':pkg'   => $prop['package_name'],
                        ':stype' => in_array($prop['package_name'], $PACKAGES, true) ? $prop['package_name'] : 'Technology Partner',
                        ':amt'   => $prop['amount'],
                        ':sdate' => date('Y-m-d'),
                        ':edate' => date('Y-m-d', strtotime('+90 days'))
                    ]);
                    $newSponsorshipId = (int)$pdo->lastInsertId();

                    // 2. Mark proposal as Accepted
                    $updProp = $pdo->prepare("UPDATE proposals SET status = 'Accepted' WHERE id = :id");
                    $updProp->execute([':id' => $proposalId]);

                    // 3. Update company status to Confirmed
                    $updComp = $pdo->prepare("UPDATE companies SET status = 'Confirmed', estimated_value = :val WHERE id = :cid");
                    $updComp->execute([':val' => $prop['amount'], ':cid' => $prop['company_id']]);

                    // 4. Log Activity
                    logActivity($pdo, $prop['company_id'], 'Sponsorship Created', "Proposal {$prop['proposal_number']} converted to Sponsorship #{$newSponsorshipId} (" . formatCurrency($prop['amount']) . ").", $currentUser['name']);

                    $pdo->commit();
                    setFlashMessage('success', "Proposal converted into Sponsorship #{$newSponsorshipId} and company marked as Confirmed!");
                    safeRedirect('sponsorships.php');
                } catch (Exception $e) {
                    $pdo->rollBack();
                    setFlashMessage('error', 'Failed to convert proposal: ' . $e->getMessage());
                }
            }
        }
        safeRedirect('proposals.php');
    }

    if ($action === 'create' || $action === 'update') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $proposalNum = trim($_POST['proposal_number'] ?? '');
        $companyId = (int)($_POST['company_id'] ?? 0);
        $contactId = !empty($_POST['contact_id']) ? (int)$_POST['contact_id'] : null;
        $packageName = trim($_POST['package_name'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $sentDate = trim($_POST['sent_date'] ?? date('Y-m-d'));
        $validUntil = trim($_POST['valid_until'] ?? date('Y-m-d', strtotime('+30 days')));
        $status = trim($_POST['status'] ?? 'Draft');
        $benefits = trim($_POST['benefits'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($companyId <= 0 || empty($packageName) || empty($sentDate) || empty($validUntil)) {
            setFlashMessage('error', 'Company, package name, sent date, and valid date are required.');
        } elseif ($validUntil < $sentDate) {
            setFlashMessage('error', 'Valid Until date must be on or after Sent date.');
        } else {
            try {
                if ($action === 'create') {
                    // Auto-generate proposal number if empty
                    if (empty($proposalNum)) {
                        $maxId = (int)$pdo->query("SELECT MAX(id) FROM proposals")->fetchColumn();
                        $proposalNum = 'P-' . (100 + $maxId + 1);
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO proposals (proposal_number, company_id, contact_id, package_name, amount, sent_date, valid_until, status, benefits, notes)
                        VALUES (:pnum, :cid, :cnt_id, :pkg, :amt, :sdate, :vdate, :status, :bene, :notes)
                    ");
                    $stmt->execute([
                        ':pnum'   => $proposalNum,
                        ':cid'    => $companyId,
                        ':cnt_id' => $contactId,
                        ':pkg'    => $packageName,
                        ':amt'    => $amount,
                        ':sdate'  => $sentDate,
                        ':vdate'  => $validUntil,
                        ':status' => $status,
                        ':bene'   => $benefits,
                        ':notes'  => $notes
                    ]);
                    logActivity($pdo, $companyId, 'Proposal Created', "Proposal {$proposalNum} created for {$packageName} (" . formatCurrency($amount) . ").", $currentUser['name']);
                    setFlashMessage('success', "Proposal {$proposalNum} created successfully.");
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE proposals 
                        SET company_id = :cid, contact_id = :cnt_id, package_name = :pkg, 
                            amount = :amt, sent_date = :sdate, valid_until = :vdate, 
                            status = :status, benefits = :bene, notes = :notes
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':cid'    => $companyId,
                        ':cnt_id' => $contactId,
                        ':pkg'    => $packageName,
                        ':amt'    => $amount,
                        ':sdate'  => $sentDate,
                        ':vdate'  => $validUntil,
                        ':status' => $status,
                        ':bene'   => $benefits,
                        ':notes'  => $notes,
                        ':id'     => $id
                    ]);
                    logActivity($pdo, $companyId, 'Proposal Updated', "Proposal {$proposalNum} updated (Status: {$status}).", $currentUser['name']);
                    setFlashMessage('success', "Proposal updated successfully.");
                }
            } catch (PDOException $e) {
                setFlashMessage('error', 'Database error: ' . $e->getMessage());
            }
        }
        safeRedirect('proposals.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM proposals WHERE id = :id");
                $stmt->execute([':id' => $id]);
                setFlashMessage('success', 'Proposal deleted.');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to delete proposal: ' . $e->getMessage());
            }
        }
        safeRedirect('proposals.php');
    }
}

// Filters
$search = trim($_GET['q'] ?? '');
$companyFilter = (int)($_GET['company_id'] ?? 0);
$statusFilter = trim($_GET['status'] ?? '');
$packageFilter = trim($_GET['package'] ?? '');

$sql = "
    SELECT p.*, c.company_name, ct.name AS contact_name,
           (SELECT id FROM sponsorships WHERE proposal_id = p.id LIMIT 1) AS converted_sponsorship_id
    FROM proposals p 
    JOIN companies c ON p.company_id = c.id 
    LEFT JOIN contacts ct ON p.contact_id = ct.id 
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.proposal_number LIKE :s1 OR p.package_name LIKE :s2 OR c.company_name LIKE :s3)";
    $term = "%{$search}%";
    $params[':s1'] = $term;
    $params[':s2'] = $term;
    $params[':s3'] = $term;
}

if ($companyFilter > 0) {
    $sql .= " AND p.company_id = :cid";
    $params[':cid'] = $companyFilter;
}

if ($statusFilter !== '') {
    $sql .= " AND p.status = :status";
    $params[':status'] = $statusFilter;
}

if ($packageFilter !== '') {
    $sql .= " AND p.package_name = :pkg";
    $params[':pkg'] = $packageFilter;
}

$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$proposalsList = $stmt->fetchAll();

$pageTitle = 'Proposals';
$activePage = 'proposals';
include __DIR__ . '/includes/header.php';
?>

<div class="head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div>
        <p class="lead" style="margin: 0; color: var(--mute);">
            Generate, negotiate, and track commercial sponsorship proposals.
        </p>
    </div>
    <div>
        <button class="btn" onclick="openAddProposalModal()">+ Create Proposal</button>
    </div>
</div>

<!-- Filters Bar -->
<form method="GET" action="proposals.php" class="tools">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search proposal #, package or company" aria-label="Search">
    
    <select name="company_id" aria-label="Filter by Company" onchange="this.form.submit()">
        <option value="">All Companies</option>
        <?php foreach ($allCompanies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= ($companyFilter === (int)$comp['id']) ? 'selected' : '' ?>>
                <?= e($comp['company_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="status" aria-label="Filter by Status" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <?php foreach ($STATUSES as $s): ?>
            <option value="<?= e($s) ?>" <?= ($statusFilter === $s) ? 'selected' : '' ?>><?= e($s) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="package" aria-label="Filter by Package" onchange="this.form.submit()">
        <option value="">All Packages</option>
        <?php foreach ($PACKAGES as $pkg): ?>
            <option value="<?= e($pkg) ?>" <?= ($packageFilter === $pkg) ? 'selected' : '' ?>><?= e($pkg) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn sm secondary">Filter</button>
    <?php if ($search !== '' || $companyFilter > 0 || $statusFilter !== '' || $packageFilter !== ''): ?>
        <a href="proposals.php" class="btn sm ghost">Clear</a>
    <?php endif; ?>
</form>

<!-- Proposals Table -->
<div class="tw">
    <table>
        <thead>
            <tr>
                <th>Proposal #</th>
                <th>Company</th>
                <th>Contact</th>
                <th>Package</th>
                <th>Amount</th>
                <th>Sent Date</th>
                <th>Valid Until</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($proposalsList)): ?>
                <?php foreach ($proposalsList as $pr): ?>
                    <tr>
                        <td><strong><?= e($pr['proposal_number']) ?></strong></td>
                        <td>
                            <strong>
                                <a href="company-details.php?id=<?= $pr['company_id'] ?>">
                                    <?= e($pr['company_name']) ?>
                                </a>
                            </strong>
                        </td>
                        <td><?= e($pr['contact_name'] ?: '-') ?></td>
                        <td><?= e($pr['package_name']) ?></td>
                        <td><strong><?= formatCurrency($pr['amount']) ?></strong></td>
                        <td><?= formatDate($pr['sent_date']) ?></td>
                        <td><?= formatDate($pr['valid_until']) ?></td>
                        <td><?= statusBadge($pr['status']) ?></td>
                        <td style="text-align: right; white-space: nowrap;">
                            <!-- Convert to Sponsorship Button -->
                            <?php if ($pr['status'] === 'Accepted' && empty($pr['converted_sponsorship_id'])): ?>
                                <form method="POST" action="proposals.php" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="convert">
                                    <input type="hidden" name="id" value="<?= $pr['id'] ?>">
                                    <button type="submit" class="btn sm" title="Convert accepted proposal to active sponsorship">Convert</button>
                                </form>
                            <?php elseif (!empty($pr['converted_sponsorship_id'])): ?>
                                <a href="sponsorships.php?q=<?= urlencode($pr['company_name']) ?>" class="badge good" style="text-decoration: none;">Converted #<?= $pr['converted_sponsorship_id'] ?></a>
                            <?php endif; ?>

                            <button type="button" class="btn sm ghost" onclick='editProposal(<?= json_encode($pr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Edit</button>
                            <form method="POST" action="proposals.php" style="display: inline;" onsubmit="return confirmDelete('Delete proposal <?= e($pr['proposal_number']) ?>?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $pr['id'] ?>">
                                <button type="submit" class="btn sm danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 2.5rem; color: var(--mute);">
                        No proposals found matching criteria.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Add / Edit Proposal -->
<dialog id="proposalDialog">
    <form method="POST" action="proposals.php" id="proposalForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="proposalFormAction" value="create">
        <input type="hidden" name="id" id="proposalId" value="">

        <h2 id="proposalModalTitle">Create Proposal</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="prop_number">Proposal # (Auto if empty)</label>
                <input id="prop_number" name="proposal_number" placeholder="e.g. P-125">
            </div>
            <div>
                <label for="prop_company">Company *</label>
                <select id="prop_company" name="company_id" required onchange="filterContactsByCompany(this.value)">
                    <option value="">-- Select Company --</option>
                    <?php foreach ($allCompanies as $comp): ?>
                        <option value="<?= $comp['id'] ?>"><?= e($comp['company_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="prop_contact">Recipient Contact</label>
                <select id="prop_contact" name="contact_id">
                    <option value="">-- Optional / General --</option>
                    <?php foreach ($allContacts as $cnt): ?>
                        <option value="<?= $cnt['id'] ?>" data-cid="<?= $cnt['company_id'] ?>">
                            <?= e($cnt['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="prop_pkg">Package Name *</label>
                <input id="prop_pkg" name="package_name" list="packageList" required placeholder="e.g. Gold Sponsor Package">
                <datalist id="packageList">
                    <?php foreach ($PACKAGES as $p): ?>
                        <option value="<?= e($p) ?> Package"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="prop_amount">Amount (₹) *</label>
                <input id="prop_amount" name="amount" type="number" min="0" step="1000" required placeholder="50000">
            </div>
            <div>
                <label for="prop_status">Status</label>
                <select id="prop_status" name="status">
                    <?php foreach ($STATUSES as $s): ?>
                        <option value="<?= e($s) ?>"><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="prop_sent">Sent Date *</label>
                <input id="prop_sent" name="sent_date" type="date" required value="<?= date('Y-m-d') ?>">
            </div>
            <div>
                <label for="prop_valid">Valid Until *</label>
                <input id="prop_valid" name="valid_until" type="date" required value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
            </div>
        </div>

        <label for="prop_bene">Benefits Offered</label>
        <textarea id="prop_bene" name="benefits" rows="2" placeholder="Backdrop logo, 10x10 stall, VIP passes..."></textarea>

        <label for="prop_notes">Internal Notes</label>
        <textarea id="prop_notes" name="notes" rows="2" placeholder="Negotiation history, special terms..."></textarea>

        <p style="margin-top: 1.5rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button type="button" class="btn ghost" onclick="closeModal('proposalDialog')">Cancel</button>
            <button type="submit" class="btn">Save Proposal</button>
        </p>
    </form>
</dialog>

<script>
const allContactsData = <?= json_encode($allContacts) ?>;

function filterContactsByCompany(companyId) {
    const contactSel = $('prop_contact');
    contactSel.innerHTML = '<option value="">-- Optional / General --</option>';
    
    allContactsData.forEach(c => {
        if (!companyId || c.company_id == companyId) {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            contactSel.appendChild(opt);
        }
    });
}

function openAddProposalModal() {
    $('proposalForm').reset();
    $('proposalFormAction').value = 'create';
    $('proposalId').value = '';
    $('proposalModalTitle').textContent = 'Create Proposal';
    $('prop_sent').value = new Date().toISOString().slice(0, 10);
    
    const d = new Date();
    d.setDate(d.getDate() + 30);
    $('prop_valid').value = d.toISOString().slice(0, 10);
    
    const urlParams = new URLSearchParams(window.location.search);
    const cid = urlParams.get('company_id');
    if (cid) {
        $('prop_company').value = cid;
        filterContactsByCompany(cid);
    }
    
    openModal('proposalDialog');
}

function editProposal(pr) {
    $('proposalForm').reset();
    $('proposalFormAction').value = 'update';
    $('proposalId').value = pr.id;
    $('proposalModalTitle').textContent = 'Edit Proposal · ' + pr.proposal_number;
    
    $('prop_number').value = pr.proposal_number || '';
    $('prop_company').value = pr.company_id || '';
    filterContactsByCompany(pr.company_id);
    $('prop_contact').value = pr.contact_id || '';
    $('prop_pkg').value = pr.package_name || '';
    $('prop_amount').value = pr.amount || 0;
    $('prop_status').value = pr.status || 'Draft';
    $('prop_sent').value = pr.sent_date || '';
    $('prop_valid').value = pr.valid_until || '';
    $('prop_bene').value = pr.benefits || '';
    $('prop_notes').value = pr.notes || '';
    
    openModal('proposalDialog');
}

window.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash === '#add') {
        openAddProposalModal();
    }
});
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>
