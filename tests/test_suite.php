<?php
/**
 * SponsorFlow - Automated Test Suite
 */

$baseUrl = 'http://127.0.0.1:8000';
$cookieFile = __DIR__ . '/test_cookies.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

$passed = 0;
$failed = 0;

function runTest($name, $callback) {
    global $passed, $failed;
    echo "Running: {$name}... ";
    try {
        $result = $callback();
        if ($result === true) {
            echo "[PASS]\n";
            $passed++;
        } else {
            echo "[FAIL] - " . (is_string($result) ? $result : 'Returned false') . "\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "[ERROR] - " . $e->getMessage() . "\n";
        $failed++;
    }
}

function httpReq($url, $method = 'GET', $data = null, $useCookies = true) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    if ($useCookies) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);
    
    return ['code' => $httpCode, 'headers' => $headers, 'body' => $body];
}

// 1. Landing page check
runTest("Landing Page (GET /index.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/index.php", 'GET', null, false);
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    if (strpos($res['body'], 'SponsorFlow') === false) return "Missing title";
    return true;
});

// 2. Auth Guard Protection on dashboard
runTest("Auth Guard on Protected Route (GET /dashboard.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/dashboard.php", 'GET', null, false);
    if ($res['code'] !== 302) return "Expected 302 redirect, got {$res['code']}";
    if (stripos($res['headers'], 'Location: login.php') === false) return "Missing Location redirect";
    return true;
});

// 3. Login page GET
runTest("Login Page (GET /login.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/login.php");
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    if (strpos($res['body'], 'Sign in to SponsorFlow') === false) return "Missing login header";
    return true;
});

// Extract CSRF token from login page
$loginRes = httpReq("{$baseUrl}/login.php");
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginRes['body'], $matches);
$csrfToken = $matches[1] ?? '';

// 4. Invalid Login attempt
runTest("Invalid Credentials Login (POST /login.php)", function() use ($baseUrl, $csrfToken) {
    $res = httpReq("{$baseUrl}/login.php", 'POST', [
        'csrf_token' => $csrfToken,
        'email' => 'admin@sponsorflow.local',
        'password' => 'WrongPassword!123'
    ]);
    if ($res['code'] !== 200) return "Expected 200 on validation fail, got {$res['code']}";
    if (strpos($res['body'], 'Invalid email address or password') === false) return "Missing error alert";
    return true;
});

// Extract fresh CSRF token
$loginRes = httpReq("{$baseUrl}/login.php");
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginRes['body'], $matches);
$csrfToken = $matches[1] ?? '';

// 5. Valid Login
runTest("Valid Authentication (POST /login.php)", function() use ($baseUrl, $csrfToken) {
    $res = httpReq("{$baseUrl}/login.php", 'POST', [
        'csrf_token' => $csrfToken,
        'email' => 'admin@sponsorflow.local',
        'password' => 'Admin@123'
    ]);
    if ($res['code'] !== 302) return "Expected 302 on successful login, got {$res['code']}";
    return true;
});

// 6. Authenticated Dashboard
runTest("Authenticated Dashboard (GET /dashboard.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/dashboard.php");
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    if (strpos($res['body'], 'Total Companies') === false) return "Missing KPI widget. Code: {$res['code']}, snippet: " . substr(strip_tags($res['body']), 0, 200);
    if (strpos($res['body'], 'Sponsorship Pipeline') === false) return "Missing pipeline section";
    return true;
});

// 7. Companies List
runTest("Companies Management (GET /companies.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/companies.php");
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    if (strpos($res['body'], 'NovaGrid Technologies') === false) return "Missing seeded company NovaGrid";
    if (strpos($res['body'], 'QuantumEdge Solutions') === false) return "Missing seeded company QuantumEdge";
    return true;
});

// 8. Company Details View
runTest("Company 360 Details (GET /company-details.php?id=1)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/company-details.php?id=1");
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    if (strpos($res['body'], 'NovaGrid Technologies') === false) return "Missing company title";
    if (strpos($res['body'], 'Key Contacts') === false) return "Missing contacts section";
    if (strpos($res['body'], 'Activity Timeline') === false) return "Missing timeline section";
    return true;
});

// 9. Contacts
runTest("Contacts Module (GET /contacts.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/contacts.php");
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    if (strpos($res['body'], 'Anita Rao') === false) return "Missing seeded contact Anita Rao";
    return true;
});

// 10. Outreach
runTest("Outreach Module (GET /outreach.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/outreach.php");
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    if (strpos($res['body'], 'Sponsorship Invitation for TechNova 2027') === false) return "Missing seeded outreach record";
    return true;
});

// 11. Follow-ups
runTest("Follow-ups Module (GET /followups.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/followups.php");
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    if (strpos($res['body'], 'Confirm final payment schedule') === false) return "Missing seeded follow-up task";
    return true;
});

// 12. Proposals
runTest("Proposals Module (GET /proposals.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/proposals.php");
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    if (strpos($res['body'], 'P-101') === false) return "Missing seeded proposal P-101";
    return true;
});

// 13. Sponsorships
runTest("Sponsorships Module (GET /sponsorships.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/sponsorships.php");
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    if (strpos($res['body'], 'Gold Sponsor Package') === false) return "Missing seeded package";
    return true;
});

// 14. Deliverables
runTest("Deliverables Module (GET /deliverables.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/deliverables.php");
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    if (strpos($res['body'], 'Logo placement on website') === false) return "Missing seeded deliverable";
    return true;
});

// 15. Reports & Analytics
runTest("Executive Reports (GET /reports.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/reports.php");
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    if (strpos($res['body'], 'Financial Overview') === false) return "Missing financial overview";
    if (strpos($res['body'], 'Conversion Rate') === false) return "Missing conversion rate metric";
    return true;
});

// 16. JSON API Endpoints
runTest("JSON API Endpoint (GET /api/companies.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/api/companies.php");
    if ($res['code'] !== 200) return "HTTP Code: {$res['code']}";
    $json = json_decode($res['body'], true);
    if (!$json || empty($json['success']) || !is_array($json['data'])) return "Invalid JSON payload";
    if (count($json['data']) < 15) return "Expected at least 15 companies, got " . count($json['data']);
    return true;
});

// 17. CRUD Test: Create, Read, Update, Delete Company
runTest("Live Database CRUD Cycle (Companies)", function() use ($baseUrl) {
    // 1. Get CSRF token from companies page
    $cRes = httpReq("{$baseUrl}/companies.php");
    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $cRes['body'], $matches);
    $csrf = $matches[1] ?? '';

    // 2. Create test company
    $createRes = httpReq("{$baseUrl}/companies.php", 'POST', [
        'csrf_token' => $csrf,
        'action' => 'create',
        'company_name' => 'Automated Test Sponsor Labs',
        'industry' => 'Cloud',
        'location' => 'Hyderabad',
        'status' => 'Interested',
        'estimated_value' => '45000',
        'notes' => 'Generated by automated test runner'
    ]);
    if ($createRes['code'] !== 302) return "Failed to create company (got {$createRes['code']})";

    // 3. Verify in database via API
    $apiRes = httpReq("{$baseUrl}/api/companies.php");
    $data = json_decode($apiRes['body'], true)['data'] ?? [];
    $found = null;
    foreach ($data as $c) {
        if ($c['company_name'] === 'Automated Test Sponsor Labs') {
            $found = $c;
            break;
        }
    }
    if (!$found) return "Newly created company not found in database";

    // 4. Update the test company
    $updateRes = httpReq("{$baseUrl}/companies.php", 'POST', [
        'csrf_token' => $csrf,
        'action' => 'update',
        'id' => $found['id'],
        'company_name' => 'Automated Test Sponsor Labs Updated',
        'industry' => 'Cloud',
        'location' => 'Hyderabad',
        'status' => 'Confirmed',
        'estimated_value' => '60000',
        'notes' => 'Updated by test runner'
    ]);
    if ($updateRes['code'] !== 302) return "Failed to update company";

    // 5. Delete the test company
    $deleteRes = httpReq("{$baseUrl}/companies.php", 'POST', [
        'csrf_token' => $csrf,
        'action' => 'delete',
        'id' => $found['id']
    ]);
    if ($deleteRes['code'] !== 302) return "Failed to delete test company";

    return true;
});

// 18. Logout
runTest("User Logout (GET /logout.php)", function() use ($baseUrl) {
    $res = httpReq("{$baseUrl}/logout.php");
    if ($res['code'] !== 302) return "Expected 302, got {$res['code']}";
    
    // Ensure dashboard is now protected again
    $dashRes = httpReq("{$baseUrl}/dashboard.php");
    if ($dashRes['code'] !== 302) return "Expected 302 redirect after logout, got {$dashRes['code']}";
    return true;
});

echo "============================================\n";
echo "Tests Passed: {$passed} | Tests Failed: {$failed}\n";
echo "============================================\n";

if (file_exists($cookieFile)) unlink($cookieFile);
exit($failed > 0 ? 1 : 0);
