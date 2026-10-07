<?php
/**
 * SponsorFlow - Core Helper Functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escapes HTML entities for safe output.
 *
 * @param mixed $value
 * @return string
 */
function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Formats a numeric amount in Indian Rupees (INR) format.
 *
 * @param float|int|string|null $amount
 * @return string
 */
function formatCurrency($amount): string {
    $num = (float)($amount ?? 0);
    return '₹' . number_format($num, 0, '.', ',');
}

/**
 * Formats date string into readable format.
 *
 * @param string|null $dateStr
 * @param string $format
 * @return string
 */
function formatDate(?string $dateStr, string $format = 'd M Y'): string {
    if (empty($dateStr)) {
        return '-';
    }
    $timestamp = strtotime($dateStr);
    return $timestamp ? date($format, $timestamp) : e($dateStr);
}

/**
 * Generates an HTML status badge styled with theme colors.
 *
 * @param string $status
 * @return string
 */
function statusBadge(string $status): string {
    $cleanStatus = e($status);
    $tone = '';
    
    $good = ['Confirmed', 'Accepted', 'Completed', 'Paid', 'Signed', 'Active', 'Responded', 'Interested', 'Strategic', 'Engaged'];
    $warn = ['Negotiation', 'Partial', 'Pending', 'In Progress', 'Viewed', 'Draft', 'Today', 'Rescheduled', 'Sent', 'Warm', 'Medium', 'On Hold', 'Delivered', 'Proposal Sent'];
    $bad  = ['Rejected', 'Overdue', 'Delayed', 'Expired', 'No Response', 'Not Interested', 'Cancelled', 'High', 'Inactive'];

    if (in_array($status, $good, true)) {
        $tone = 'good';
    } elseif (in_array($status, $warn, true)) {
        $tone = 'warn';
    } elseif (in_array($status, $bad, true)) {
        $tone = 'bad';
    }

    return '<span class="badge ' . $tone . '">' . $cleanStatus . '</span>';
}

/**
 * Calculates priority badge.
 *
 * @param string $priority
 * @return string
 */
function priorityBadge(string $priority): string {
    $p = strtolower($priority);
    $cls = ($p === 'high') ? 'bad' : (($p === 'medium') ? 'warn' : 'good');
    return '<span class="badge ' . $cls . '">' . e($priority) . '</span>';
}

/**
 * Records an activity timeline entry for a company.
 *
 * @param PDO $pdo
 * @param int $companyId
 * @param string $type
 * @param string $description
 * @param string $createdBy
 * @return void
 */
function logActivity(PDO $pdo, int $companyId, string $type, string $description, string $createdBy = 'System'): void {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activities (company_id, activity_type, description, activity_date, created_by)
            VALUES (:company_id, :activity_type, :description, :activity_date, :created_by)
        ");
        $stmt->execute([
            ':company_id'    => $companyId,
            ':activity_type' => $type,
            ':description'   => $description,
            ':activity_date' => date('Y-m-d'),
            ':created_by'    => $createdBy
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Sets a session flash message.
 *
 * @param string $type ('success', 'error', 'info', 'warn')
 * @param string $message
 * @return void
 */
function setFlashMessage(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Retrieves and clears the flash message.
 *
 * @return array|null
 */
function getFlashMessage(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Safely redirects to a local URL.
 *
 * @param string $url
 * @return void
 */
function safeRedirect(string $url): void {
    // Prevent header injection and open redirect outside application
    $cleanUrl = str_replace(["\r", "\n"], '', $url);
    if (!preg_match('#^[a-zA-Z0-9_\-\./\?=&]+$#', $cleanUrl)) {
        $cleanUrl = 'dashboard.php';
    }
    header("Location: " . $cleanUrl);
    exit;
}

/**
 * Sends a clean JSON response.
 *
 * @param array $data
 * @param int $status
 * @return void
 */
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Validates 10-digit Indian mobile number.
 *
 * @param string $phone
 * @return bool
 */
function isValidPhone(string $phone): bool {
    $clean = preg_replace('/\D/', '', $phone);
    return (bool)preg_match('/^[6-9]\d{9}$/', $clean);
}

/**
 * Validates email address format.
 *
 * @param string $email
 * @return bool
 */
function isValidEmail(string $email): bool {
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}
