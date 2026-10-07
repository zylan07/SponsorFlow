<?php
/**
 * SponsorFlow - Proposals JSON API Endpoint
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
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT p.*, c.company_name FROM proposals p JOIN companies c ON p.company_id = c.id WHERE p.id = :id");
        $stmt->execute([':id' => $id]);
        $prop = $stmt->fetch();
        if ($prop) {
            jsonResponse(['success' => true, 'data' => $prop]);
        } else {
            jsonResponse(['success' => false, 'error' => 'Proposal not found.'], 404);
        }
    } else {
        $stmt = $pdo->query("SELECT p.*, c.company_name FROM proposals p JOIN companies c ON p.company_id = c.id ORDER BY p.id DESC");
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
    }
}

jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);
