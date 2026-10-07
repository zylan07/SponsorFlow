<?php
/**
 * SponsorFlow - Outreach Communications Module (CRUD)
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();
$pdo = getDBConnection();
$currentUser = currentUser();

$TYPES = ['Email', 'Phone Call', 'WhatsApp', 'LinkedIn', 'Meeting'];
$STATUSES = ['Sent', 'Delivered', 'Responded', 'Interested', 'No Response', 'Not Interested'];

// Fetch companies & contacts
$allCompanies = $pdo->query("SELECT id, company_name FROM companies ORDER BY company_name ASC")->fetchAll();
$allContacts = $pdo->query("SELECT id, company_id, name FROM contacts ORDER BY name ASC")->fetchAll();

// Handle POST actions: create, update, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $companyId = (int)($_POST['company_id'] ?? 0);
        $contactId = !empty($_POST['contact_id']) ? (int)$_POST['contact_id'] : null;
        $type = trim($_POST['outreach_type'] ?? 'Email');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $date = trim($_POST['outreach_date'] ?? date('Y-m-d'));
        $status = trim($_POST['status'] ?? 'Sent');
        $response = trim($_POST['response'] ?? '');
        $assignedTo = trim($_POST['assigned_to'] ?? $currentUser['name']);
        $nextAction = trim($_POST['next_action'] ?? '');

        if ($companyId <= 0 || empty($subject) || empty($date)) {
            setFlashMessage('error', 'Company, subject, and date are required.');
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $pdo->prepare("
                        INSERT INTO outreach (company_id, contact_id, outreach_type, subject, message, outreach_date, status, response, assigned_to, next_action)
                        VALUES (:cid, :cnt_id, :type, :subj, :msg, :odate, :status, :resp, :assign, :next_act)
                    ");
                    $stmt->execute([
                        ':cid'      => $companyId,
                        ':cnt_id'   => $contactId,
                        ':type'     => $type,
                        ':subj'     => $subject,
                        ':msg'      => $message,
                        ':odate'    => $date,
                        ':status'   => $status,
                        ':resp'     => $response,
                        ':assign'   => $assignedTo,
                        ':next_act' => $nextAction
                    ]);

                    // Update contact's last_contacted date
                    if ($contactId) {
                        $cUpdate = $pdo->prepare("UPDATE contacts SET last_contacted = :odate WHERE id = :cid");
                        $cUpdate->execute([':odate' => $date, ':cid' => $contactId]);
                    }

                    logActivity($pdo, $companyId, 'Outreach Logged', "{$type}: '{$subject}' logged ({$status}).", $currentUser['name']);
                    setFlashMessage('success', 'Outreach interaction logged successfully.');
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE outreach 
                        SET company_id = :cid, contact_id = :cnt_id, outreach_type = :type, 
                            subject = :subj, message = :msg, outreach_date = :odate, 
                            status = :status, response = :resp, assigned_to = :assign, next_action = :next_act
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':cid'      => $companyId,
                        ':cnt_id'   => $contactId,
                        ':type'     => $type,
                        ':subj'     => $subject,
                        ':msg'      => $message,
                        ':odate'    => $date,
                        ':status'   => $status,
                        ':resp'     => $response,
                        ':assign'   => $assignedTo,
                        ':next_act' => $nextAction,
                        ':id'       => $id
                    ]);
                    logActivity($pdo, $companyId, 'Outreach Updated', "{$type}: '{$subject}' updated.", $currentUser['name']);
                    setFlashMessage('success', 'Outreach record updated successfully.');
                }
            } catch (PDOException $e) {
                setFlashMessage('error', 'Database error: ' . $e->getMessage());
            }
        }
        safeRedirect('outreach.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM outreach WHERE id = :id");
                $stmt->execute([':id' => $id]);
                setFlashMessage('success', 'Outreach record deleted.');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to delete record: ' . $e->getMessage());
            }
        }
        safeRedirect('outreach.php');
    }
}

// Filters
$search = trim($_GET['q'] ?? '');
$companyFilter = (int)($_GET['company_id'] ?? 0);
$typeFilter = trim($_GET['type'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$sql = "
    SELECT o.*, c.company_name, ct.name AS contact_name 
    FROM outreach o 
    JOIN companies c ON o.company_id = c.id 
    LEFT JOIN contacts ct ON o.contact_id = ct.id 
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (o.subject LIKE :s1 OR o.message LIKE :s2 OR o.assigned_to LIKE :s3 OR c.company_name LIKE :s4)";
    $term = "%{$search}%";
    $params[':s1'] = $term;
    $params[':s2'] = $term;
    $params[':s3'] = $term;
    $params[':s4'] = $term;
}

if ($companyFilter > 0) {
    $sql .= " AND o.company_id = :cid";
    $params[':cid'] = $companyFilter;
}

if ($typeFilter !== '') {
    $sql .= " AND o.outreach_type = :type";
    $params[':type'] = $typeFilter;
}

if ($statusFilter !== '') {
    $sql .= " AND o.status = :status";
    $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY o.outreach_date DESC, o.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$outreachList = $stmt->fetchAll();

$pageTitle = 'Outreach';
$activePage = 'outreach';
include __DIR__ . '/includes/header.php';
?>

<div class="head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div>
        <p class="lead" style="margin: 0; color: var(--mute);">
            Every pitch, email, phone call, and sponsor meeting.
        </p>
    </div>
    <div>
        <button class="btn" onclick="openAddOutreachModal()">+ Log Outreach</button>
    </div>
</div>

<!-- Filters Bar -->
<form method="GET" action="outreach.php" class="tools">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search subject, notes or owner" aria-label="Search">
    
    <select name="company_id" aria-label="Filter by Company" onchange="this.form.submit()">
        <option value="">All Companies</option>
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

    <select name="status" aria-label="Filter by Status" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <?php foreach ($STATUSES as $s): ?>
            <option value="<?= e($s) ?>" <?= ($statusFilter === $s) ? 'selected' : '' ?>><?= e($s) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn sm secondary">Filter</button>
    <?php if ($search !== '' || $companyFilter > 0 || $typeFilter !== '' || $statusFilter !== ''): ?>
        <a href="outreach.php" class="btn sm ghost">Clear</a>
    <?php endif; ?>
</form>

<!-- Outreach Table -->
<div class="tw">
    <table>
        <thead>
            <tr>
                <th>Company</th>
                <th>Contact</th>
                <th>Type</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
                <th>Owner</th>
                <th>Next Action</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($outreachList)): ?>
                <?php foreach ($outreachList as $item): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="company-details.php?id=<?= $item['company_id'] ?>">
                                    <?= e($item['company_name']) ?>
                                </a>
                            </strong>
                        </td>
                        <td><?= e($item['contact_name'] ?: '-') ?></td>
                        <td><span class="badge"><?= e($item['outreach_type']) ?></span></td>
                        <td><strong><?= e($item['subject']) ?></strong></td>
                        <td><?= formatDate($item['outreach_date']) ?></td>
                        <td><?= statusBadge($item['status']) ?></td>
                        <td><?= e($item['assigned_to']) ?></td>
                        <td><?= e($item['next_action'] ?: '-') ?></td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button type="button" class="btn sm ghost" onclick='editOutreach(<?= json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Edit</button>
                            <form method="POST" action="outreach.php" style="display: inline;" onsubmit="return confirmDelete('Are you sure you want to delete this outreach record?');">
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
                    <td colspan="9" style="text-align: center; padding: 2.5rem; color: var(--mute);">
                        No outreach records found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Add / Edit Outreach -->
<dialog id="outreachDialog">
    <form method="POST" action="outreach.php" id="outreachForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="outreachFormAction" value="create">
        <input type="hidden" name="id" id="outreachId" value="">

        <h2 id="outreachModalTitle">Log Outreach Communication</h2>

        <label for="out_company">Company *</label>
        <select id="out_company" name="company_id" required onchange="filterContactsByCompany(this.value)">
            <option value="">-- Select Company --</option>
            <?php foreach ($allCompanies as $comp): ?>
                <option value="<?= $comp['id'] ?>"><?= e($comp['company_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="out_contact">Contact Person</label>
        <select id="out_contact" name="contact_id">
            <option value="">-- Optional / General --</option>
            <?php foreach ($allContacts as $cnt): ?>
                <option value="<?= $cnt['id'] ?>" data-cid="<?= $cnt['company_id'] ?>">
                    <?= e($cnt['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="out_type">Communication Channel *</label>
                <select id="out_type" name="outreach_type" required>
                    <?php foreach ($TYPES as $t): ?>
                        <option value="<?= e($t) ?>"><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="out_date">Interaction Date *</label>
                <input id="out_date" name="outreach_date" type="date" required value="<?= date('Y-m-d') ?>">
            </div>
        </div>

        <label for="out_subject">Subject / Purpose *</label>
        <input id="out_subject" name="subject" required placeholder="e.g. Follow-up on TechNova sponsorship deck">

        <label for="out_message">Message Summary / Notes</label>
        <textarea id="out_message" name="message" rows="2" placeholder="Details of the conversation..."></textarea>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="out_status">Status</label>
                <select id="out_status" name="status">
                    <?php foreach ($STATUSES as $s): ?>
                        <option value="<?= e($s) ?>"><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="out_assigned">Assigned Member</label>
                <input id="out_assigned" name="assigned_to" required value="<?= e($currentUser['name']) ?>">
            </div>
        </div>

        <label for="out_response">Sponsor Response</label>
        <textarea id="out_response" name="response" rows="2" placeholder="Sponsor replied requesting customized tier..."></textarea>

        <label for="out_next">Next Action</label>
        <input id="out_next" name="next_action" placeholder="e.g. Send revised contract draft by Friday">

        <p style="margin-top: 1.5rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button type="button" class="btn ghost" onclick="closeModal('outreachDialog')">Cancel</button>
            <button type="submit" class="btn">Save Record</button>
        </p>
    </form>
</dialog>

<script>
const allContactsData = <?= json_encode($allContacts) ?>;

function filterContactsByCompany(companyId) {
    const contactSel = $('out_contact');
    const curVal = contactSel.value;
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

function openAddOutreachModal() {
    $('outreachForm').reset();
    $('outreachFormAction').value = 'create';
    $('outreachId').value = '';
    $('outreachModalTitle').textContent = 'Log Outreach Communication';
    $('out_date').value = new Date().toISOString().slice(0, 10);
    $('out_assigned').value = <?= json_encode($currentUser['name']) ?>;
    
    const urlParams = new URLSearchParams(window.location.search);
    const cid = urlParams.get('company_id');
    if (cid) {
        $('out_company').value = cid;
        filterContactsByCompany(cid);
    }
    
    openModal('outreachDialog');
}

function editOutreach(item) {
    $('outreachForm').reset();
    $('outreachFormAction').value = 'update';
    $('outreachId').value = item.id;
    $('outreachModalTitle').textContent = 'Edit Outreach · ' + item.subject;
    
    $('out_company').value = item.company_id || '';
    filterContactsByCompany(item.company_id);
    $('out_contact').value = item.contact_id || '';
    $('out_type').value = item.outreach_type || 'Email';
    $('out_date').value = item.outreach_date || '';
    $('out_subject').value = item.subject || '';
    $('out_message').value = item.message || '';
    $('out_status').value = item.status || 'Sent';
    $('out_assigned').value = item.assigned_to || '';
    $('out_response').value = item.response || '';
    $('out_next').value = item.next_action || '';
    
    openModal('outreachDialog');
}

window.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash === '#add') {
        openAddOutreachModal();
    }
});
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>
