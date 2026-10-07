<?php
/**
 * SponsorFlow - Companies Management Module (CRUD)
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();
$pdo = getDBConnection();
$currentUser = currentUser();

$STATUSES = ['Prospect', 'Contacted', 'Proposal Sent', 'Interested', 'Negotiation', 'Confirmed', 'Rejected'];
$INDUSTRIES = ['IT Services', 'Cloud', 'Data & AI', 'Fintech', 'Education', 'Retail', 'Manufacturing', 'Healthcare', 'Automotive', 'Other'];

// Handle POST actions: create, update, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = trim($_POST['company_name'] ?? '');
        $industry = trim($_POST['industry'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = trim($_POST['status'] ?? 'Prospect');
        $value = (float)($_POST['estimated_value'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        
        // Optional primary contact person to auto-create on new company
        $contactName = trim($_POST['contact_name'] ?? '');
        $contactDesignation = trim($_POST['contact_designation'] ?? 'Primary Contact');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');

        // Server-side validation
        if (empty($name) || empty($location) || empty($industry)) {
            setFlashMessage('error', 'Company name, industry, and location are required.');
        } elseif (!in_array($status, $STATUSES, true)) {
            setFlashMessage('error', 'Invalid status selected.');
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $pdo->prepare("
                        INSERT INTO companies (company_name, industry, location, website, phone, email, status, estimated_value, notes)
                        VALUES (:name, :industry, :location, :website, :phone, :email, :status, :value, :notes)
                    ");
                    $stmt->execute([
                        ':name'     => $name,
                        ':industry' => $industry,
                        ':location' => $location,
                        ':website'  => $website,
                        ':phone'    => $phone,
                        ':email'    => $email,
                        ':status'   => $status,
                        ':value'    => $value,
                        ':notes'    => $notes
                    ]);
                    $newCompanyId = (int)$pdo->lastInsertId();

                    // If contact was provided, create contact record
                    if (!empty($contactName)) {
                        $cStmt = $pdo->prepare("
                            INSERT INTO contacts (company_id, name, designation, email, phone, relationship_status, notes)
                            VALUES (:cid, :cname, :cdesig, :cemail, :cphone, 'New', 'Primary contact added during company creation')
                        ");
                        $cStmt->execute([
                            ':cid'    => $newCompanyId,
                            ':cname'  => $contactName,
                            ':cdesig' => $contactDesignation,
                            ':cemail' => $contactEmail ?: $email,
                            ':cphone' => $contactPhone ?: $phone
                        ]);
                    }

                    logActivity($pdo, $newCompanyId, 'Company Created', "Company '{$name}' created with status '{$status}'.", $currentUser['name']);
                    setFlashMessage('success', "Company '{$name}' created successfully.");
                } else {
                    // Update existing
                    $stmt = $pdo->prepare("
                        UPDATE companies 
                        SET company_name = :name, industry = :industry, location = :location, 
                            website = :website, phone = :phone, email = :email, status = :status, 
                            estimated_value = :value, notes = :notes
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':name'     => $name,
                        ':industry' => $industry,
                        ':location' => $location,
                        ':website'  => $website,
                        ':phone'    => $phone,
                        ':email'    => $email,
                        ':status'   => $status,
                        ':value'    => $value,
                        ':notes'    => $notes,
                        ':id'       => $id
                    ]);

                    logActivity($pdo, $id, 'Company Updated', "Company details updated. Status: '{$status}'.", $currentUser['name']);
                    setFlashMessage('success', "Company '{$name}' updated successfully.");
                }
            } catch (PDOException $e) {
                setFlashMessage('error', 'Database error: ' . $e->getMessage());
            }
        }
        safeRedirect('companies.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM companies WHERE id = :id");
                $stmt->execute([':id' => $id]);
                setFlashMessage('success', 'Company and all associated records deleted successfully.');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to delete company: ' . $e->getMessage());
            }
        }
        safeRedirect('companies.php');
    }
}

// Fetch query filters
$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$industryFilter = trim($_GET['industry'] ?? '');

$sql = "
    SELECT c.*, 
           (SELECT name FROM contacts WHERE company_id = c.id ORDER BY id ASC LIMIT 1) AS primary_contact,
           (SELECT followup_date FROM followups WHERE company_id = c.id AND status != 'Completed' ORDER BY followup_date ASC LIMIT 1) AS next_followup
    FROM companies c
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (c.company_name LIKE :s1 OR c.location LIKE :s2 OR c.email LIKE :s3 OR EXISTS (
        SELECT 1 FROM contacts ct WHERE ct.company_id = c.id AND ct.name LIKE :s4
    ))";
    $term = "%{$search}%";
    $params[':s1'] = $term;
    $params[':s2'] = $term;
    $params[':s3'] = $term;
    $params[':s4'] = $term;
}

if ($statusFilter !== '') {
    $sql .= " AND c.status = :status";
    $params[':status'] = $statusFilter;
}

if ($industryFilter !== '') {
    $sql .= " AND c.industry = :industry";
    $params[':industry'] = $industryFilter;
}

$sql .= " ORDER BY c.company_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$companiesList = $stmt->fetchAll();

$pageTitle = 'Companies';
$activePage = 'companies';
include __DIR__ . '/includes/header.php';
?>

<div class="head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div>
        <p class="lead" style="margin: 0; color: var(--mute);">
            Manage sponsor prospects, partners, and corporate relationships.
        </p>
    </div>
    <div>
        <button class="btn" onclick="openAddCompanyModal()">+ Add Company</button>
    </div>
</div>

<!-- Filters Bar -->
<form method="GET" action="companies.php" class="tools">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search company, location or contact" aria-label="Search">
    <select name="status" aria-label="Filter by Status" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <?php foreach ($STATUSES as $st): ?>
            <option value="<?= e($st) ?>" <?= ($statusFilter === $st) ? 'selected' : '' ?>><?= e($st) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="industry" aria-label="Filter by Industry" onchange="this.form.submit()">
        <option value="">All Industries</option>
        <?php foreach ($INDUSTRIES as $ind): ?>
            <option value="<?= e($ind) ?>" <?= ($industryFilter === $ind) ? 'selected' : '' ?>><?= e($ind) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn sm secondary">Filter</button>
    <?php if ($search !== '' || $statusFilter !== '' || $industryFilter !== ''): ?>
        <a href="companies.php" class="btn sm ghost">Clear</a>
    <?php endif; ?>
</form>

<!-- Companies Table -->
<div class="tw">
    <table>
        <thead>
            <tr>
                <th>Company</th>
                <th>Industry</th>
                <th>Location</th>
                <th>Primary Contact</th>
                <th>Status</th>
                <th>Estimated Value</th>
                <th>Next Follow-up</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($companiesList)): ?>
                <?php foreach ($companiesList as $comp): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="company-details.php?id=<?= $comp['id'] ?>">
                                    <?= e($comp['company_name']) ?>
                                </a>
                            </strong>
                            <?php if (!empty($comp['website'])): ?>
                                <small style="display: block; color: var(--mute);"><?= e($comp['website']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($comp['industry']) ?></td>
                        <td><?= e($comp['location']) ?></td>
                        <td><?= e($comp['primary_contact'] ?: '-') ?></td>
                        <td><?= statusBadge($comp['status']) ?></td>
                        <td><strong><?= formatCurrency($comp['estimated_value']) ?></strong></td>
                        <td><?= formatDate($comp['next_followup']) ?></td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="company-details.php?id=<?= $comp['id'] ?>" class="btn sm ghost">View</a>
                            <button type="button" class="btn sm ghost" onclick='editCompany(<?= json_encode($comp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Edit</button>
                            <form method="POST" action="companies.php" style="display: inline;" onsubmit="return confirmDelete('Are you sure you want to delete <?= e(addslashes($comp['company_name'])) ?>? This will cascade delete its contacts, outreach, and proposals.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $comp['id'] ?>">
                                <button type="submit" class="btn sm danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--mute);">
                        No companies found matching your search criteria.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Add / Edit Company -->
<dialog id="companyDialog">
    <form method="POST" action="companies.php" id="companyForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="formAction" value="create">
        <input type="hidden" name="id" id="companyId" value="">

        <h2 id="modalTitle">Add New Company</h2>

        <label for="comp_name">Company Name *</label>
        <input id="comp_name" name="company_name" required placeholder="e.g. Apex Innovations">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="comp_industry">Industry *</label>
                <select id="comp_industry" name="industry" required>
                    <?php foreach ($INDUSTRIES as $ind): ?>
                        <option value="<?= e($ind) ?>"><?= e($ind) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="comp_location">Location *</label>
                <input id="comp_location" name="location" required placeholder="e.g. Chennai">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="comp_status">Pipeline Stage *</label>
                <select id="comp_status" name="status" required>
                    <?php foreach ($STATUSES as $st): ?>
                        <option value="<?= e($st) ?>"><?= e($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="comp_value">Estimated Value (₹)</label>
                <input id="comp_value" name="estimated_value" type="number" min="0" step="1000" placeholder="0">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label for="comp_website">Website</label>
                <input id="comp_website" name="website" placeholder="www.example.com">
            </div>
            <div>
                <label for="comp_phone">Company Phone</label>
                <input id="comp_phone" name="phone" placeholder="9840123456">
            </div>
        </div>

        <label for="comp_email">General Email</label>
        <input id="comp_email" name="email" type="email" placeholder="contact@example.com">

        <!-- Initial Contact Details (for new companies only) -->
        <div id="contactFieldsSection" style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed var(--line);">
            <strong style="font-size: 0.88rem; color: var(--pri);">Primary Contact Person (Optional)</strong>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div>
                    <label for="cont_name">Contact Name</label>
                    <input id="cont_name" name="contact_name" placeholder="e.g. Anita Rao">
                </div>
                <div>
                    <label for="cont_designation">Designation</label>
                    <input id="cont_designation" name="contact_designation" placeholder="e.g. Marketing Lead">
                </div>
            </div>
        </div>

        <label for="comp_notes">Notes / Background</label>
        <textarea id="comp_notes" name="notes" rows="2" placeholder="Introduced via alumni network..."></textarea>

        <p style="margin-top: 1.5rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button type="button" class="btn ghost" onclick="closeModal('companyDialog')">Cancel</button>
            <button type="submit" class="btn" id="saveButton">Save Company</button>
        </p>
    </form>
</dialog>

<script>
function openAddCompanyModal() {
    $('companyForm').reset();
    $('formAction').value = 'create';
    $('companyId').value = '';
    $('modalTitle').textContent = 'Add New Company';
    $('contactFieldsSection').style.display = 'block';
    openModal('companyDialog');
}

function editCompany(comp) {
    $('companyForm').reset();
    $('formAction').value = 'update';
    $('companyId').value = comp.id;
    $('modalTitle').textContent = 'Edit Company · ' + comp.company_name;
    
    $('comp_name').value = comp.company_name || '';
    $('comp_industry').value = comp.industry || '';
    $('comp_location').value = comp.location || '';
    $('comp_status').value = comp.status || 'Prospect';
    $('comp_value').value = comp.estimated_value || 0;
    $('comp_website').value = comp.website || '';
    $('comp_phone').value = comp.phone || '';
    $('comp_email').value = comp.email || '';
    $('comp_notes').value = comp.notes || '';
    
    // Hide contact creation on edit to avoid duplicate contacts
    $('contactFieldsSection').style.display = 'none';
    
    openModal('companyDialog');
}
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>
