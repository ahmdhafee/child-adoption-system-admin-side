<?php
declare(strict_types=1);

require_once '../officer_auth.php';
require_once '../officer_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Access denied']);
    exit;
}

function ok(array $data=[]): void { echo json_encode(array_merge(['success'=>true],$data)); exit; }
function fail(string $m,int $c=400): void { http_response_code($c); echo json_encode(['success'=>false,'message'=>$m]); exit; }

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {

    // ✅ LIST CLIENTS
    if ($action === 'list') {

        // Your table from screenshot: id, email, registration_id, created_at, last_login, status
        $stmt = $pdo->query("
            SELECT
                id AS user_id,
                email,
                registration_id,
                created_at,
                last_login,
                status AS user_status
            FROM users
            ORDER BY id DESC
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build response fields that your JS expects
        $clients = array_map(function($r){
            $email = (string)($r['email'] ?? '');
            $name = $email;
            if (strpos($email, '@') !== false) {
                $name = explode('@', $email)[0]; // name from email (ex: ahamedhafeel29)
            }

            return [
                'user_id' => $r['user_id'],
                'name' => $name,                           // ✅ JS needs name
                'email' => $r['email'],
                'registration_id' => $r['registration_id'] ?? '-',
                'user_status' => $r['user_status'] ?? 'pending',

                // ✅ These fields are not in your DB now, so return safe defaults:
                'eligibility_score' => 0,
                'application_status' => 'pending',
                'docs_total' => 0,
                'docs_approved' => 0,
                'has_voted' => false,
                'appointment_count' => 0,
            ];
        }, $rows);

        ok(['clients' => $clients]);
    }

    // ✅ TOGGLE ACTIVE/SUSPEND
    if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $user_id = (int)($_POST['user_id'] ?? 0);
        if ($user_id <= 0) fail('Invalid user_id');

        $cur = $pdo->prepare("SELECT status FROM users WHERE id = ?");
        $cur->execute([$user_id]);
        $status = $cur->fetchColumn();

        if (!$status) fail('Client not found', 404);

        // you currently use: active (screenshot). We will toggle between active <-> suspended
        $newStatus = ($status === 'active') ? 'suspended' : 'active';

        $upd = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $upd->execute([$newStatus, $user_id]);

        ok(['message' => "Client status updated to $newStatus"]);
    }

    fail('Unknown action');

} catch (PDOException $e) {
    error_log("clients_api error: ".$e->getMessage());
    fail('Server error', 500);
}
