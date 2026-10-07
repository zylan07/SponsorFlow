<?php
/**
 * SponsorFlow - Follow-ups Management Module
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();
$pdo = getDBConnection();
$currentUser = currentUser();

$PRIORITIES = ['High', 'Medium', 'Low'];
$STATUS_OPTIONS = ['Pending', 'Completed', 'Rescheduled'];

// Companies & contacts
$allCompanies = $pdo->query("SELECT id, company_name FROM companies ORDER BY company_name ASC")->fetchAll();
$allContacts = $pdo->query("SELECT id, company_id, name FROM contacts ORDER BY name ASC")->fetchAll();

// Handle POST actions: create, update, delete, complete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
    $action = $_POST['action'] ?? '';

    if ($action === 'complete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE followups SET status = 'Completed' WHERE id = :id");
            $stmt->execute([':id' => $id]);
            setFlashMessage('success', 'Follow-up marked as Completed.');
        }
        safeRedirect('followups.php');
    }

    if ($action === 'create' || $action === 'update') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $companyId = (int)($_POST['company_id'] ?? 0);
        $contactId = !empty($_POST['contact_id']) ? (int)$_POST['contact_id'] : null;
        $date = trim($_POST['followup_date'] ?? date('Y-m-d'));
        $priority = trim($_POST['priority'] ?? 'Medium');
        $reason = trim($_POST['reason'] ?? '');
        $status = trim($_POST['status'] ?? 'Pending');
        $assignedTo = trim($_POST['assigned_to'] ?? $currentUser['name']);
        $notes = trim($_POST['notes'] ?? '');

        if ($companyId <= 0 || empty($reason) || empty($date)) {
            setFlashMessage('error', 'Company, reason, and follow-up date are required.');
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $pdo->prepare("
                        INSERT INTO followups (company_id, contact_id, followup_date, priority, reason, status, assigned_to, notes)
                        VALUES (:cid, :cnt_id, :fdate, :prio, :reason, :status, :assign, :notes)
                    ");
                    $stmt->execute([
                        ':cid'     => $companyId,
                        ':cnt_id'  => $contactId,
                        ':fdate'   => $date,
                        ':prio'    => $priority,
                        ':reason'  => $reason,
                        ':status'  => $status,
                        ':assign'  => $assignedTo,
                        ':notes'   => $notes
                    ]);
                    logActivity($pdo, $companyId, 'Follow-up Scheduled', "Follow-up scheduled for {$date}: {$reason}", $currentUser['name']);
                    setFlashMessage('success', 'Follow-up task scheduled successfully.');
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE followups 
                        SET company_id = :cid, contact_id = :cnt_id, followup_date = :fdate, 
                            priority = :prio, reason = :reason, status = :status, 
                            assigned_to = :assign, notes = :notes
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':cid'     => $companyId,
                        ':cnt_id'  => $contactId,
                        ':fdate'   => $date,
                        ':prio'    => $priority,
                        ':reason'  => $reason,
                        ':status'  => $status,
                        ':assign'  => $assignedTo,
                        ':notes'   => $notes,
                        ':id'      => $id
                    ]);
                    logActivity($pdo, $companyId, 'Follow-up Updated', "Follow-up updated: {$reason}", $currentUser['name']);
                    setFlashMessage('success', 'Follow-up task updated.');
                }
            } catch (PDOException $e) {
                setFlashMessage('error', 'Database error: ' . $e->getMessage());
            }
        }
        safeRedirect('followups.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM followups WHERE id = :id");
                $stmt->execute([':id' => $id]);
                setFlashMessage('success', 'Follow-up task removed.');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to delete task: ' . $e->getMessage());
            }
        }
        safeRedirect('followups.php');
    }
}

// Filters
$search = trim($_GET['q'] ?? '');
$companyFilter = (int)($_GET['company_id'] ?? 0);
$priorityFilter = trim($_GET['priority'] ?? '');
$timingFilter = trim($_GET['timing'] ?? ''); // 'overdue', 'today', 'upcoming', 'completed'

$today = date('Y-m-d');
$sql = "
    SELECT f.*, c.company_name, ct.name AS contact_name 
    FROM followups f 
    JOIN companies c ON f.company_id = c.id 
    LEFT JOIN contacts ct ON f.contact_id = ct.id 
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (f.reason LIKE :s1 OR f.notes LIKE :s2 OR f.assigned_to LIKE :s3 OR c.company_name LIKE :s4)";
    $term = "%{$search}%";
    $params[':s1'] = $term;
    $params[':s2'] = $term;
    $params[':s3'] = $term;
    $params[':s4'] = $term;
}

if ($companyFilter > 0) {
    $sql .= " AND f.company_id = :cid";
    $params[':cid'] = $companyFilter;
}

if ($priorityFilter !== '') {
    $sql .= " AND f.priority = :prio";
    $params[':prio'] = $priorityFilter;
}

if ($timingFilter === 'overdue') {
    $sql .= " AND f.status != 'Completed' AND f.followup_date < '{$today}'";
} elseif ($timingFilter === 'today') {
    $sql .= " AND f.status != 'Completed' AND f.followup_date = '{$today}'";
} elseif ($timingFilter === 'upcoming') {
    $sql .= " AND f.status != 'Completed' AND f.followup_date > '{$today}'";
} elseif ($timingFilter === 'completed') {
    $sql .= " AND f.status = 'Completed'";
}

$sql .= " ORDER BY f.followup_date ASC, f.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$followupsList = $stmt->fetchAll();

$pageTitle = 'Follow-ups';
$activePage = 'followups';
include __DIR__ . '/includes/header.php';
?>

<div class="head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div>
        <p class="lead" style="margin: 0; color: var(--mute);">
            Automatic tracking of overdue, due-today, and upcoming sponsor deliverables.
        </p>
    </div>
    <div>
        <button class="btn" onclick="openAddFollowupModal()">+ Schedule Follow-up</button>
    </div>
</div>

<!-- Filters Bar -->
<form method="GET" action="followups.php" class="tools">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search task reason, notes or owner" aria-label="Search">
    
    <select name="timing" aria-label="Filter by Timing" onchange="this.form.submit()">
        <option value="">All Timeline Stages</option>
        <option value="overdue" <?= ($timingFilter === 'overdue') ? 'selected' : '' ?>>Overdue</option>
        <option value="today" <?= ($timingFilter === 'today') ? 'selected' : '' ?>>Due Today</option>
        <option value="upcoming" <?= ($timingFilter === 'upcoming') ? 'selected' : '' ?>>Upcoming</option>
        <option value="completed" <?= ($timingFilter === 'completed') ? 'selected' : '' ?>>Completed</option>
    </select>

    <select name="company_id" aria-label="Filter by Company" onchange="this.form.submit()">
        <option value="">All Companies</option>
        <?php foreach ($allCompanies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= ($companyFilter === (int)$comp['id']) ? 'selected' : '' ?>>
                <?= e($comp['company_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="priority" aria-label="Filter by Priority" onchange="this.form.submit()">
        <option value="">All Priorities</option>
        <?php foreach ($PRIORITIES as $p): ?>
            <option value="<?= e($p) ?>" <?= ($priorityFilter === $p) ? 'selected' : '' ?>><?= e($p) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn sm secondary">Filter</button>
    <?php if ($search !== '' || $companyFilter > 0 || $priorityFilter !== '' || $timingFilter !== ''): ?>
        <a href="followups.php" class="btn sm ghost">Clear</a>
    <?php endif; ?>
</form>

<!-- Follow-ups Table -->
<div class="tw">
    <table>
        <thead>
            <tr>
                <th>Company</th>
                <th>Contact</th>
                <th>Due Date</th>
                <th>Priority</th>
                <th>Reason / Objective</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($followupsList)): ?>
                <?php foreach ($followupsList as $item): ?>
                    <?php
                        $isPast = ($item['followup_date'] < $today);
                        $isToday = ($item['followup_date'] === $today);
                        $displayStatus = $item['status'];
                        if ($item['status'] !== 'Completed') {
                            $displayStatus = $isPast ? 'Overdue' : ($isToday ? 'Today' : 'Upcoming');
                        }
                    ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="company-details.php?id=<?= $item['company_id'] ?>">
                                    <?= e($item['company_name']) ?>
                                </a>
                            </strong>
                        </td>
                        <td><?= e($item['contact_name'] ?: '-') ?></td>
                        <td>
                            <strong><?= formatDate($item['followup_date']) ?></strong>
                        </td>
                        <td><?= priorityBadge($item['priority']) ?></td>
                        <td><?= e($item['reason']) ?></td>
                        <td><?= statusBadge($displayStatus) ?></td>
                        <td><?= e($item['assigned_to']) ?></td>
                        <td style="text-align: right; white-space: nowrap;">
                            <?php if ($item['status'] !== 'Completed'): ?>
                                <form method="POST" action="followups.php" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="complete">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn sm" title="Mark as Completed">Complete</button>
                                </form>
                            <?php endif; ?>
                            <button type="button" class="btn sm ghost" onclick='editFollowup(<?= json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Edit</button>
                            <form method="POST" action="followups.php" style="display: inline;" onsubmit="return confirmDelete('Delete this follow-up task?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn sm danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--mute);">
                        No follow-ups found matching your selection.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Add / Edit Follow-up -->
<dialog id="followupDialog">
    <form method="POST" action="followups.php" id="followupForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="followupFormAction" value="create">
        <input type="hidden" name="id" id="followupId" value="">

        <h2 id="followupModalTitle">Schedule Follow-up Task</h2>

        <label for="fu_company">Company *</label>
        <select id="fu_company" name="company_id" required onchange="filterContactsByCompany(this.value)">
            <option value="">-- Select Company --</option>
            <?php foreach ($allCompanies as $comp): ?>
                <option value="<?= $comp['id'] ?>"><?= e($comp['company_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="fu_contact">Contact Person</label>
        <select id="fu_contact" name="contact_id">
            <option value="">-- Optional / General --</option>
            <?php foreach ($allContacts as $cnt): ?>
                <option value="<?= $cnt['id'] ?>" data-cid="<?= $cnt['company_id'] ?>">
                    <?= e($cnt['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="fu_date">Follow-up Date *</label>
                <input id="fu_date" name="followup_date" type="date" required value="<?= date('Y-m-d') ?>">
            </div>
            <div>
                <label for="fu_priority">Priority</label>
                <select id="fu_priority" name="priority">
                    <?php foreach ($PRIORITIES as $p): ?>
                        <option value="<?= e($p) ?>" <?= ($p === 'Medium') ? 'selected' : '' ?>><?= e($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <label for="fu_reason">Reason / Objective *</label>
        <input id="fu_reason" name="reason" required placeholder="e.g. Call to finalize keynote slot and draft invoice">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="fu_status">Status</label>
                <select id="fu_status" name="status">
                    <?php foreach ($STATUS_OPTIONS as $so): ?>
                        <option value="<?= e($so) ?>"><?= e($so) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="fu_assign">Assigned To</label>
                <input id="fu_assign" name="assigned_to" required value="<?= e($currentUser['name']) ?>">
            </div>
        </div>

        <label for="fu_notes">Notes</label>
        <textarea id="fu_notes" name="notes" rows="2" placeholder="Any context or preparation reminders..."></textarea>

        <p style="margin-top: 1.5rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button type="button" class="btn ghost" onclick="closeModal('followupDialog')">Cancel</button>
            <button type="submit" class="btn">Save Task</button>
        </p>
    </form>
</dialog>

<script>
const allContactsData = <?= json_encode($allContacts) ?>;

function filterContactsByCompany(companyId) {
    const contactSel = $('fu_contact');
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

function openAddFollowupModal() {
    $('followupForm').reset();
    $('followupFormAction').value = 'create';
    $('followupId').value = '';
    $('followupModalTitle').textContent = 'Schedule Follow-up Task';
    $('fu_date').value = new Date().toISOString().slice(0, 10);
    $('fu_assign').value = <?= json_encode($currentUser['name']) ?>;
    
    const urlParams = new URLSearchParams(window.location.search);
    const cid = urlParams.get('company_id');
    if (cid) {
        $('fu_company').value = cid;
        filterContactsByCompany(cid);
    }
    
    openModal('followupDialog');
}

function editFollowup(item) {
    $('followupForm').reset();
    $('followupFormAction').value = 'update';
    $('followupId').value = item.id;
    $('followupModalTitle').textContent = 'Edit Follow-up';
    
    $('fu_company').value = item.company_id || '';
    filterContactsByCompany(item.company_id);
    $('fu_contact').value = item.contact_id || '';
    $('fu_date').value = item.followup_date || '';
    $('fu_priority').value = item.priority || 'Medium';
    $('fu_reason').value = item.reason || '';
    $('fu_status').value = item.status || 'Pending';
    $('fu_assign').value = item.assigned_to || '';
    $('fu_notes').value = item.notes || '';
    
    openModal('followupDialog');
}

window.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash === '#add') {
        openAddFollowupModal();
    }
});
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>
