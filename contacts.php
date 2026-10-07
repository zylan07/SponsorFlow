<?php
/**
 * SponsorFlow - Contacts Management Module (CRUD)
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();
$pdo = getDBConnection();
$currentUser = currentUser();

$METHODS = ['Email', 'Phone Call', 'WhatsApp', 'LinkedIn', 'Meeting'];
$RELATIONSHIPS = ['New', 'Warm', 'Engaged', 'Strategic', 'Inactive'];

// Fetch all companies for select dropdowns
$companiesStmt = $pdo->query("SELECT id, company_name FROM companies ORDER BY company_name ASC");
$allCompanies = $companiesStmt->fetchAll();

// Handle POST actions: create, update, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $companyId = (int)($_POST['company_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $designation = trim($_POST['designation'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $preferredContact = trim($_POST['preferred_contact'] ?? 'Email');
        $relationshipStatus = trim($_POST['relationship_status'] ?? 'New');
        $lastContacted = !empty($_POST['last_contacted']) ? $_POST['last_contacted'] : null;
        $notes = trim($_POST['notes'] ?? '');

        // Server-side validation
        if (empty($name) || $companyId <= 0 || empty($designation)) {
            setFlashMessage('error', 'Contact name, company, and designation are required.');
        } elseif (!isValidEmail($email)) {
            setFlashMessage('error', 'Please provide a valid email address.');
        } elseif (!isValidPhone($phone)) {
            setFlashMessage('error', 'Please provide a valid 10-digit Indian mobile number.');
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $pdo->prepare("
                        INSERT INTO contacts (company_id, name, designation, email, phone, preferred_contact, relationship_status, last_contacted, notes)
                        VALUES (:cid, :name, :desig, :email, :phone, :pref, :rel, :last_c, :notes)
                    ");
                    $stmt->execute([
                        ':cid'    => $companyId,
                        ':name'   => $name,
                        ':desig'  => $designation,
                        ':email'  => $email,
                        ':phone'  => $phone,
                        ':pref'   => $preferredContact,
                        ':rel'    => $relationshipStatus,
                        ':last_c' => $lastContacted,
                        ':notes'  => $notes
                    ]);
                    logActivity($pdo, $companyId, 'Contact Added', "New contact '{$name}' ({$designation}) added.", $currentUser['name']);
                    setFlashMessage('success', "Contact '{$name}' created successfully.");
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE contacts 
                        SET company_id = :cid, name = :name, designation = :desig, email = :email, 
                            phone = :phone, preferred_contact = :pref, relationship_status = :rel, 
                            last_contacted = :last_c, notes = :notes
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':cid'    => $companyId,
                        ':name'   => $name,
                        ':desig'  => $designation,
                        ':email'  => $email,
                        ':phone'  => $phone,
                        ':pref'   => $preferredContact,
                        ':rel'    => $relationshipStatus,
                        ':last_c' => $lastContacted,
                        ':notes'  => $notes,
                        ':id'     => $id
                    ]);
                    logActivity($pdo, $companyId, 'Contact Updated', "Contact '{$name}' details updated.", $currentUser['name']);
                    setFlashMessage('success', "Contact '{$name}' updated successfully.");
                }
            } catch (PDOException $e) {
                setFlashMessage('error', 'Database error: ' . $e->getMessage());
            }
        }
        safeRedirect('contacts.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = :id");
                $stmt->execute([':id' => $id]);
                setFlashMessage('success', 'Contact deleted successfully.');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to delete contact: ' . $e->getMessage());
            }
        }
        safeRedirect('contacts.php');
    }
}

// Filters
$search = trim($_GET['q'] ?? '');
$companyFilter = (int)($_GET['company_id'] ?? 0);
$relFilter = trim($_GET['relationship'] ?? '');

$sql = "
    SELECT ct.*, c.company_name 
    FROM contacts ct 
    JOIN companies c ON ct.company_id = c.id 
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (ct.name LIKE :s1 OR ct.email LIKE :s2 OR ct.phone LIKE :s3 OR ct.designation LIKE :s4 OR c.company_name LIKE :s5)";
    $term = "%{$search}%";
    $params[':s1'] = $term;
    $params[':s2'] = $term;
    $params[':s3'] = $term;
    $params[':s4'] = $term;
    $params[':s5'] = $term;
}

if ($companyFilter > 0) {
    $sql .= " AND ct.company_id = :cid";
    $params[':cid'] = $companyFilter;
}

if ($relFilter !== '') {
    $sql .= " AND ct.relationship_status = :rel";
    $params[':rel'] = $relFilter;
}

$sql .= " ORDER BY ct.name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$contactsList = $stmt->fetchAll();

$pageTitle = 'Contacts';
$activePage = 'contacts';
include __DIR__ . '/includes/header.php';
?>

<div class="head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div>
        <p class="lead" style="margin: 0; color: var(--mute);">
            Key stakeholders, coordinators, and brand representatives.
        </p>
    </div>
    <div>
        <button class="btn" onclick="openAddContactModal()">+ Add Contact</button>
    </div>
</div>

<!-- Filters Bar -->
<form method="GET" action="contacts.php" class="tools">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search contact name, email, role or company" aria-label="Search">
    
    <select name="company_id" aria-label="Filter by Company" onchange="this.form.submit()">
        <option value="">All Companies</option>
        <?php foreach ($allCompanies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= ($companyFilter === (int)$comp['id']) ? 'selected' : '' ?>>
                <?= e($comp['company_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="relationship" aria-label="Filter by Relationship" onchange="this.form.submit()">
        <option value="">All Relationships</option>
        <?php foreach ($RELATIONSHIPS as $rel): ?>
            <option value="<?= e($rel) ?>" <?= ($relFilter === $rel) ? 'selected' : '' ?>><?= e($rel) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn sm secondary">Filter</button>
    <?php if ($search !== '' || $companyFilter > 0 || $relFilter !== ''): ?>
        <a href="contacts.php" class="btn sm ghost">Clear</a>
    <?php endif; ?>
</form>

<!-- Contacts Table -->
<div class="tw">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Company</th>
                <th>Designation</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Preferred</th>
                <th>Relationship</th>
                <th>Last Contacted</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($contactsList)): ?>
                <?php foreach ($contactsList as $cnt): ?>
                    <tr>
                        <td><strong><?= e($cnt['name']) ?></strong></td>
                        <td>
                            <a href="company-details.php?id=<?= $cnt['company_id'] ?>">
                                <?= e($cnt['company_name']) ?>
                            </a>
                        </td>
                        <td><?= e($cnt['designation']) ?></td>
                        <td><a href="mailto:<?= e($cnt['email']) ?>"><?= e($cnt['email']) ?></a></td>
                        <td><?= e($cnt['phone']) ?></td>
                        <td><?= e($cnt['preferred_contact']) ?></td>
                        <td><?= statusBadge($cnt['relationship_status']) ?></td>
                        <td><?= formatDate($cnt['last_contacted']) ?></td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button type="button" class="btn sm ghost" onclick='editContact(<?= json_encode($cnt, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Edit</button>
                            <form method="POST" action="contacts.php" style="display: inline;" onsubmit="return confirmDelete('Are you sure you want to delete this contact?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $cnt['id'] ?>">
                                <button type="submit" class="btn sm danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 2.5rem; color: var(--mute);">
                        No contacts found matching your search.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Add / Edit Contact -->
<dialog id="contactDialog">
    <form method="POST" action="contacts.php" id="contactForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="contactFormAction" value="create">
        <input type="hidden" name="id" id="contactId" value="">

        <h2 id="contactModalTitle">Add New Contact</h2>

        <label for="cnt_name">Full Name *</label>
        <input id="cnt_name" name="name" required placeholder="e.g. Dr. Rajesh Sharma">

        <label for="cnt_company">Company *</label>
        <select id="cnt_company" name="company_id" required>
            <option value="">-- Select Company --</option>
            <?php foreach ($allCompanies as $comp): ?>
                <option value="<?= $comp['id'] ?>"><?= e($comp['company_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="cnt_desig">Designation / Role *</label>
        <input id="cnt_desig" name="designation" required placeholder="e.g. Vice President, HR">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="cnt_email">Email Address *</label>
                <input id="cnt_email" name="email" type="email" required placeholder="name@company.com">
            </div>
            <div>
                <label for="cnt_phone">Phone (10 digits) *</label>
                <input id="cnt_phone" name="phone" required placeholder="9840123456">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="cnt_pref">Preferred Channel</label>
                <select id="cnt_pref" name="preferred_contact">
                    <?php foreach ($METHODS as $m): ?>
                        <option value="<?= e($m) ?>"><?= e($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="cnt_rel">Relationship Status</label>
                <select id="cnt_rel" name="relationship_status">
                    <?php foreach ($RELATIONSHIPS as $r): ?>
                        <option value="<?= e($r) ?>"><?= e($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <label for="cnt_last">Last Contacted Date</label>
        <input id="cnt_last" name="last_contacted" type="date">

        <label for="cnt_notes">Notes</label>
        <textarea id="cnt_notes" name="notes" rows="2" placeholder="Key interests, best time to call..."></textarea>

        <p style="margin-top: 1.5rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button type="button" class="btn ghost" onclick="closeModal('contactDialog')">Cancel</button>
            <button type="submit" class="btn">Save Contact</button>
        </p>
    </form>
</dialog>

<script>
function openAddContactModal() {
    $('contactForm').reset();
    $('contactFormAction').value = 'create';
    $('contactId').value = '';
    $('contactModalTitle').textContent = 'Add New Contact';
    
    // Auto-select if url had company_id
    const urlParams = new URLSearchParams(window.location.search);
    const cid = urlParams.get('company_id');
    if (cid) {
        $('cnt_company').value = cid;
    }
    
    openModal('contactDialog');
}

function editContact(cnt) {
    $('contactForm').reset();
    $('contactFormAction').value = 'update';
    $('contactId').value = cnt.id;
    $('contactModalTitle').textContent = 'Edit Contact · ' + cnt.name;
    
    $('cnt_name').value = cnt.name || '';
    $('cnt_company').value = cnt.company_id || '';
    $('cnt_desig').value = cnt.designation || '';
    $('cnt_email').value = cnt.email || '';
    $('cnt_phone').value = cnt.phone || '';
    $('cnt_pref').value = cnt.preferred_contact || 'Email';
    $('cnt_rel').value = cnt.relationship_status || 'New';
    $('cnt_last').value = cnt.last_contacted || '';
    $('cnt_notes').value = cnt.notes || '';
    
    openModal('contactDialog');
}

// Auto open if #add hash is present
window.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash === '#add') {
        openAddContactModal();
    }
});
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>
