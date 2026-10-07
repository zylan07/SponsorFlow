<?php
/**
 * SponsorFlow - Deliverables Fulfillment Module (CRUD & Progress Tracking)
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();
$pdo = getDBConnection();
$currentUser = currentUser();

$PRIORITIES = ['High', 'Medium', 'Low'];
$STATUSES = ['Not Started', 'In Progress', 'Completed', 'Delayed'];

$allCompanies = $pdo->query("SELECT id, company_name FROM companies ORDER BY company_name ASC")->fetchAll();
$allSponsorships = $pdo->query("SELECT id, company_id, package_name FROM sponsorships ORDER BY id DESC")->fetchAll();

// Handle POST actions: create, update, delete, complete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
    $action = $_POST['action'] ?? '';

    // Quick action: Mark deliverable complete
    if ($action === 'complete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE deliverables 
                SET status = 'Completed', completion_date = :cdate 
                WHERE id = :id
            ");
            $stmt->execute([':cdate' => date('Y-m-d'), ':id' => $id]);
            setFlashMessage('success', 'Deliverable marked as Completed!');
        }
        safeRedirect('deliverables.php');
    }

    if ($action === 'create' || $action === 'update') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $companyId = (int)($_POST['company_id'] ?? 0);
        $sponsorshipId = !empty($_POST['sponsorship_id']) ? (int)$_POST['sponsorship_id'] : null;
        $deliverable = trim($_POST['deliverable'] ?? '');
        $eventName = trim($_POST['event_name'] ?? 'TechNova 2027');
        $dueDate = trim($_POST['due_date'] ?? date('Y-m-d', strtotime('+14 days')));
        $owner = trim($_POST['owner'] ?? $currentUser['name']);
        $priority = trim($_POST['priority'] ?? 'Medium');
        $status = trim($_POST['status'] ?? 'Not Started');
        $completionDate = ($status === 'Completed') ? (!empty($_POST['completion_date']) ? $_POST['completion_date'] : date('Y-m-d')) : null;
        $notes = trim($_POST['notes'] ?? '');

        if ($companyId <= 0 || empty($deliverable) || empty($dueDate)) {
            setFlashMessage('error', 'Sponsor company, deliverable description, and due date are required.');
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $pdo->prepare("
                        INSERT INTO deliverables (company_id, sponsorship_id, deliverable, event_name, due_date, owner, priority, status, completion_date, notes)
                        VALUES (:cid, :spid, :deliv, :event, :ddate, :owner, :prio, :status, :cdate, :notes)
                    ");
                    $stmt->execute([
                        ':cid'    => $companyId,
                        ':spid'   => $sponsorshipId,
                        ':deliv'  => $deliverable,
                        ':event'  => $eventName,
                        ':ddate'  => $dueDate,
                        ':owner'  => $owner,
                        ':prio'   => $priority,
                        ':status' => $status,
                        ':cdate'  => $completionDate,
                        ':notes'  => $notes
                    ]);
                    logActivity($pdo, $companyId, 'Deliverable Added', "Deliverable added: '{$deliverable}' (Due: {$dueDate}).", $currentUser['name']);
                    setFlashMessage('success', 'Deliverable added successfully.');
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE deliverables 
                        SET company_id = :cid, sponsorship_id = :spid, deliverable = :deliv, 
                            event_name = :event, due_date = :ddate, owner = :owner, 
                            priority = :prio, status = :status, completion_date = :cdate, notes = :notes
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':cid'    => $companyId,
                        ':spid'   => $sponsorshipId,
                        ':deliv'  => $deliverable,
                        ':event'  => $eventName,
                        ':ddate'  => $dueDate,
                        ':owner'  => $owner,
                        ':prio'   => $priority,
                        ':status' => $status,
                        ':cdate'  => $completionDate,
                        ':notes'  => $notes,
                        ':id'     => $id
                    ]);
                    logActivity($pdo, $companyId, 'Deliverable Updated', "Deliverable '{$deliverable}' updated (Status: {$status}).", $currentUser['name']);
                    setFlashMessage('success', 'Deliverable updated successfully.');
                }
            } catch (PDOException $e) {
                setFlashMessage('error', 'Database error: ' . $e->getMessage());
            }
        }
        safeRedirect('deliverables.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM deliverables WHERE id = :id");
                $stmt->execute([':id' => $id]);
                setFlashMessage('success', 'Deliverable deleted.');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to delete deliverable: ' . $e->getMessage());
            }
        }
        safeRedirect('deliverables.php');
    }
}

// Filters
$search = trim($_GET['q'] ?? '');
$companyFilter = (int)($_GET['company_id'] ?? 0);
$statusFilter = trim($_GET['status'] ?? '');
$priorityFilter = trim($_GET['priority'] ?? '');

$sql = "
    SELECT d.*, c.company_name, s.package_name 
    FROM deliverables d 
    JOIN companies c ON d.company_id = c.id 
    LEFT JOIN sponsorships s ON d.sponsorship_id = s.id 
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (d.deliverable LIKE :s1 OR d.owner LIKE :s2 OR c.company_name LIKE :s3)";
    $term = "%{$search}%";
    $params[':s1'] = $term;
    $params[':s2'] = $term;
    $params[':s3'] = $term;
}

if ($companyFilter > 0) {
    $sql .= " AND d.company_id = :cid";
    $params[':cid'] = $companyFilter;
}

if ($statusFilter !== '') {
    $sql .= " AND d.status = :status";
    $params[':status'] = $statusFilter;
}

if ($priorityFilter !== '') {
    $sql .= " AND d.priority = :prio";
    $params[':prio'] = $priorityFilter;
}

$sql .= " ORDER BY d.due_date ASC, d.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$deliverablesList = $stmt->fetchAll();

// Progress Calculation
$totalCount = count($deliverablesList);
$completedCount = 0;
foreach ($deliverablesList as $del) {
    if ($del['status'] === 'Completed') $completedCount++;
}
$completionRate = ($totalCount > 0) ? round(($completedCount / $totalCount) * 100) : 0;

$pageTitle = 'Deliverables';
$activePage = 'deliverables';
include __DIR__ . '/includes/header.php';
?>

<div class="head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div>
        <p class="lead" style="margin: 0; color: var(--mute);">
            Fulfillment and on-ground execution of contractual sponsor deliverables.
        </p>
    </div>
    <div>
        <button class="btn" onclick="openAddDeliverableModal()">+ Add Deliverable</button>
    </div>
</div>

<!-- Progress Card -->
<div class="card" style="margin-bottom: 1.25rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
        <div>
            <strong style="font-size: 1.15rem;"><?= $completionRate ?>% Complete</strong>
            <span style="color: var(--mute); margin-left: 0.5rem;">(<?= $completedCount ?> of <?= $totalCount ?> deliverables fulfilled)</span>
        </div>
        <span class="badge good"><?= $completedCount ?> Fulfilled</span>
    </div>
    <span class="prog"><i style="width: <?= $completionRate ?>%;"></i></span>
</div>

<!-- Filters Bar -->
<form method="GET" action="deliverables.php" class="tools">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search deliverable, owner or company" aria-label="Search">
    
    <select name="company_id" aria-label="Filter by Sponsor" onchange="this.form.submit()">
        <option value="">All Sponsors</option>
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

    <select name="priority" aria-label="Filter by Priority" onchange="this.form.submit()">
        <option value="">All Priorities</option>
        <?php foreach ($PRIORITIES as $p): ?>
            <option value="<?= e($p) ?>" <?= ($priorityFilter === $p) ? 'selected' : '' ?>><?= e($p) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn sm secondary">Filter</button>
    <?php if ($search !== '' || $companyFilter > 0 || $statusFilter !== '' || $priorityFilter !== ''): ?>
        <a href="deliverables.php" class="btn sm ghost">Clear</a>
    <?php endif; ?>
</form>

<!-- Deliverables Table -->
<div class="tw">
    <table>
        <thead>
            <tr>
                <th>Deliverable</th>
                <th>Sponsor Company</th>
                <th>Event</th>
                <th>Due Date</th>
                <th>Owner</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Completed On</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($deliverablesList)): ?>
                <?php foreach ($deliverablesList as $del): ?>
                    <tr>
                        <td>
                            <strong><?= e($del['deliverable']) ?></strong>
                            <?php if (!empty($del['package_name'])): ?>
                                <small style="display: block; color: var(--mute);"><?= e($del['package_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong>
                                <a href="company-details.php?id=<?= $del['company_id'] ?>">
                                    <?= e($del['company_name']) ?>
                                </a>
                            </strong>
                        </td>
                        <td><?= e($del['event_name']) ?></td>
                        <td><?= formatDate($del['due_date']) ?></td>
                        <td><?= e($del['owner']) ?></td>
                        <td><?= priorityBadge($del['priority']) ?></td>
                        <td>
                            <?= statusBadge($del['status']) ?>
                            <?php
                                $fill = match($del['status']) {
                                    'Completed'   => 100,
                                    'In Progress' => 50,
                                    'Delayed'     => 25,
                                    default       => 0
                                };
                            ?>
                            <span class="mini"><i style="width: <?= $fill ?>%;"></i></span>
                        </td>
                        <td><?= formatDate($del['completion_date']) ?></td>
                        <td style="text-align: right; white-space: nowrap;">
                            <?php if ($del['status'] !== 'Completed'): ?>
                                <form method="POST" action="deliverables.php" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="complete">
                                    <input type="hidden" name="id" value="<?= $del['id'] ?>">
                                    <button type="submit" class="btn sm" title="Mark deliverable complete">Mark Done</button>
                                </form>
                            <?php endif; ?>

                            <button type="button" class="btn sm ghost" onclick='editDeliverable(<?= json_encode($del, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Edit</button>
                            <form method="POST" action="deliverables.php" style="display: inline;" onsubmit="return confirmDelete('Are you sure you want to delete this deliverable?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $del['id'] ?>">
                                <button type="submit" class="btn sm danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 2.5rem; color: var(--mute);">
                        No deliverables found matching your query.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Add / Edit Deliverable -->
<dialog id="deliverableDialog">
    <form method="POST" action="deliverables.php" id="deliverableForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="delivFormAction" value="create">
        <input type="hidden" name="id" id="delivId" value="">

        <h2 id="delivModalTitle">Add Deliverable</h2>

        <label for="d_name">Deliverable Title *</label>
        <input id="d_name" name="deliverable" required placeholder="e.g. 10x10 ft stall space & main stage banner logo">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="d_company">Sponsor Company *</label>
                <select id="d_company" name="company_id" required onchange="filterSponsorshipsByCompany(this.value)">
                    <option value="">-- Select Company --</option>
                    <?php foreach ($allCompanies as $comp): ?>
                        <option value="<?= $comp['id'] ?>"><?= e($comp['company_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="d_spid">Associated Sponsorship</label>
                <select id="d_spid" name="sponsorship_id">
                    <option value="">-- Optional / Direct --</option>
                    <?php foreach ($allSponsorships as $sp): ?>
                        <option value="<?= $sp['id'] ?>" data-cid="<?= $sp['company_id'] ?>">
                            #<?= $sp['id'] ?> - <?= e($sp['package_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="d_event">Event Name</label>
                <input id="d_event" name="event_name" value="TechNova 2027" required>
            </div>
            <div>
                <label for="d_due">Due Date *</label>
                <input id="d_due" name="due_date" type="date" required value="<?= date('Y-m-d', strtotime('+14 days')) ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="d_owner">Owner / Responsible</label>
                <input id="d_owner" name="owner" required value="<?= e($currentUser['name']) ?>">
            </div>
            <div>
                <label for="d_prio">Priority</label>
                <select id="d_prio" name="priority">
                    <?php foreach ($PRIORITIES as $p): ?>
                        <option value="<?= e($p) ?>" <?= ($p === 'Medium') ? 'selected' : '' ?>><?= e($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="d_status">Status</label>
                <select id="d_status" name="status">
                    <?php foreach ($STATUSES as $s): ?>
                        <option value="<?= e($s) ?>"><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <label for="d_notes">Execution Notes</label>
        <textarea id="d_notes" name="notes" rows="2" placeholder="Specifications, print vendor instructions..."></textarea>

        <p style="margin-top: 1.5rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button type="button" class="btn ghost" onclick="closeModal('deliverableDialog')">Cancel</button>
            <button type="submit" class="btn">Save Deliverable</button>
        </p>
    </form>
</dialog>

<script>
const allSponsorshipsData = <?= json_encode($allSponsorships) ?>;

function filterSponsorshipsByCompany(companyId) {
    const spSel = $('d_spid');
    spSel.innerHTML = '<option value="">-- Optional / Direct --</option>';
    
    allSponsorshipsData.forEach(sp => {
        if (!companyId || sp.company_id == companyId) {
            const opt = document.createElement('option');
            opt.value = sp.id;
            opt.textContent = '#' + sp.id + ' - ' + sp.package_name;
            spSel.appendChild(opt);
        }
    });
}

function openAddDeliverableModal() {
    $('deliverableForm').reset();
    $('delivFormAction').value = 'create';
    $('delivId').value = '';
    $('delivModalTitle').textContent = 'Add Deliverable';
    $('d_event').value = 'TechNova 2027';
    $('d_owner').value = <?= json_encode($currentUser['name']) ?>;
    
    const d = new Date();
    d.setDate(d.getDate() + 14);
    $('d_due').value = d.toISOString().slice(0, 10);
    
    const urlParams = new URLSearchParams(window.location.search);
    const cid = urlParams.get('company_id');
    if (cid) {
        $('d_company').value = cid;
        filterSponsorshipsByCompany(cid);
    }
    
    openModal('deliverableDialog');
}

function editDeliverable(del) {
    $('deliverableForm').reset();
    $('delivFormAction').value = 'update';
    $('delivId').value = del.id;
    $('delivModalTitle').textContent = 'Edit Deliverable';
    
    $('d_name').value = del.deliverable || '';
    $('d_company').value = del.company_id || '';
    filterSponsorshipsByCompany(del.company_id);
    $('d_spid').value = del.sponsorship_id || '';
    $('d_event').value = del.event_name || 'TechNova 2027';
    $('d_due').value = del.due_date || '';
    $('d_owner').value = del.owner || '';
    $('d_prio').value = del.priority || 'Medium';
    $('d_status').value = del.status || 'Not Started';
    $('d_notes').value = del.notes || '';
    
    openModal('deliverableDialog');
}

window.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash === '#add') {
        openAddDeliverableModal();
    }
});
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>
