<?php
/**
 * SponsorFlow - Sponsorships JSON API Endpoint
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
        $stmt = $pdo->prepare("SELECT s.*, c.company_name FROM sponsorships s JOIN companies c ON s.company_id = c.id WHERE s.id = :id");
        $stmt->execute([':id' => $id]);
        $sp = $stmt->fetch();
        if ($sp) {
            jsonResponse(['success' => true, 'data' => $sp]);
        } else {
            jsonResponse(['success' => false, 'error' => 'Sponsorship not found.'], 404);
        }
    } else {
        $stmt = $pdo->query("SELECT s.*, c.company_name FROM sponsorships s JOIN companies c ON s.company_id = c.id ORDER BY s.id DESC");
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
    }
}

jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);
