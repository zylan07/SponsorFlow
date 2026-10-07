<?php
/**
 * SponsorFlow - Outreach JSON API Endpoint
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'Unauthorized access.'], 401);
}

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $companyId = isset($_GET['company_id']) ? (int)$_GET['company_id'] : 0;
    if ($companyId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM outreach WHERE company_id = :cid ORDER BY outreach_date DESC");
        $stmt->execute([':cid' => $companyId]);
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
    } else {
        $stmt = $pdo->query("SELECT o.*, c.company_name FROM outreach o JOIN companies c ON o.company_id = c.id ORDER BY o.outreach_date DESC");
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
    }
}

jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);
