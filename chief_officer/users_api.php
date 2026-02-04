<?php
declare(strict_types=1);

require_once '../officer_auth.php';
require_once '../officer_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'chief') {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Access denied']);
    exit;
}

function ok(array $data=[]): void {
    echo json_encode(array_merge(['success'=>true], $data));
    exit;
}
function fail(string $msg, int $code=400): void {
    http_response_code($code);
    echo json_encode(['success'=>false,'message'=>$msg]);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {

    // ---------------- LIST ----------------
    if ($action === 'list') {

        // Clients (couples) from users table
        // name: try pull from applications partner1/2 else email
        $clientsStmt = $pdo->query("
            SELECT u.id AS uid,
                   u.email,
                   u.status,
                   u.created_at,
                   u.last_login,
                   a.partner1_name,
                   a.partner2_name
            FROM users u
            LEFT JOIN applications a ON a.user_id = u.id
            ORDER BY u.created_at DESC
        ");
        $clients = [];
        foreach ($clientsStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $name = trim(($r['partner1_name'] ?? '') . ' ' . ($r['partner2_name'] ?? ''));
            if ($name === '') $name = $r['email'];

            // users.status: active | pending | suspended
            $clients[] = [
                'uid' => (string)$r['uid'],
                'source' => 'client',          // important for actions
                'name' => $name,
                'email' => $r['email'],
                'role' => 'couple',
                'status' => $r['status'] ?? 'pending',
                'created_at' => $r['created_at'],
                'last_login' => $r['last_login'],
            ];
        }

        // Officers (admin/chief) from officers table
        $officersStmt = $pdo->query("
            SELECT id AS uid, email, full_name, role, status, created_at, last_login
            FROM officers
            ORDER BY created_at DESC
        ");
        $officers = [];
        foreach ($officersStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $officers[] = [
                'uid' => (string)$r['uid'],
                'source' => 'officer',
                'name' => $r['full_name'] ?? $r['email'],
                'email' => $r['email'],
                'role' => $r['role'], // admin or chief
                'status' => $r['status'],
                'created_at' => $r['created_at'],
                'last_login' => $r['last_login'],
            ];
        }

        // Merge list
        $users = array_merge($officers, $clients);
        ok(['users'=>$users]);
    }

    // ---------------- CREATE ----------------
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName  = trim($_POST['lastName'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $role      = trim($_POST['role'] ?? 'couple');
        $status    = trim($_POST['status'] ?? 'active');

        if ($firstName === '' || $lastName === '' || $email === '') {
            fail('Missing required fields');
        }

        // only chief can create admin; do NOT allow creating another chief via UI
        if ($role === 'chief') {
            fail('Creating Chief accounts is not allowed.');
        }

        // Create OFFICER (admin)
        if ($role === 'admin') {
            $tempPassword = 'Admin@' . random_int(1000, 9999);
            $hash = password_hash($tempPassword, PASSWORD_DEFAULT);

            $status = ($status === 'active') ? 'active' : 'suspended';

            $stmt = $pdo->prepare("
                INSERT INTO officers (email, full_name, password_hash, role, status, created_by)
                VALUES (?, ?, ?, 'admin', ?, ?)
            ");
            $stmt->execute([
                $email,
                $firstName . ' ' . $lastName,
                $hash,
                $status,
                $_SESSION['officer_id'] ?? null
            ]);

            ok(['message'=>'Admin created', 'tempPassword'=>$tempPassword]);
        }

        // Create CLIENT (couple) in users table
        // Your users table requires registration_id, password, email, status
        if ($role === 'couple') {
            $tempPassword = 'User@' . random_int(1000, 9999);
            $hash = password_hash($tempPassword, PASSWORD_DEFAULT);

            $statusAllowed = ['active','pending','suspended'];
            if (!in_array($status, $statusAllowed, true)) $status = 'pending';

            // registration_id format
            $reg = 'FB-' . date('Y') . '-' . random_int(100000, 999999);

            $stmt = $pdo->prepare("
                INSERT INTO users (email, password, registration_id, status)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$email, $hash, $reg, $status]);

            ok(['message'=>'Couple user created', 'tempPassword'=>$tempPassword]);
        }

        fail('Invalid role');
    }

    // ---------------- TOGGLE STATUS ----------------
    if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $uid = (int)($_POST['uid'] ?? 0);
        $source = $_POST['source'] ?? '';

        if ($uid <= 0 || !in_array($source, ['client','officer'], true)) {
            fail('Invalid request');
        }

        if ($source === 'client') {
            $row = $pdo->prepare("SELECT status FROM users WHERE id = ? LIMIT 1");
            $row->execute([$uid]);
            $u = $row->fetch(PDO::FETCH_ASSOC);
            if (!$u) fail('User not found');

            $new = ($u['status'] === 'active') ? 'suspended' : 'active';

            $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$new, $uid]);
            ok(['message'=>"Client status updated to $new"]);
        }

        if ($source === 'officer') {
            $row = $pdo->prepare("SELECT status, role FROM officers WHERE id = ? LIMIT 1");
            $row->execute([$uid]);
            $o = $row->fetch(PDO::FETCH_ASSOC);
            if (!$o) fail('Officer not found');

            // Safety: do not suspend yourself accidentally
            if ((int)$uid === (int)($_SESSION['officer_id'] ?? -1)) {
                fail('You cannot change your own status.');
            }

            $new = ($o['status'] === 'active') ? 'suspended' : 'active';
            $pdo->prepare("UPDATE officers SET status = ? WHERE id = ?")->execute([$new, $uid]);
            ok(['message'=>"Officer status updated to $new"]);
        }
    }

    // ---------------- DELETE ----------------
    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $uid = (int)($_POST['uid'] ?? 0);
        $source = $_POST['source'] ?? '';

        if ($uid <= 0 || !in_array($source, ['client','officer'], true)) {
            fail('Invalid request');
        }

        if ($source === 'officer' && (int)$uid === (int)($_SESSION['officer_id'] ?? -1)) {
            fail('You cannot delete your own account.');
        }

        if ($source === 'client') {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
            ok(['message'=>'Client deleted']);
        }

        if ($source === 'officer') {
            $pdo->prepare("DELETE FROM officers WHERE id = ?")->execute([$uid]);
            ok(['message'=>'Officer deleted']);
        }
    }

    fail('Unknown action');

} catch (PDOException $e) {
    error_log("users_api error: " . $e->getMessage());
    fail('Server error', 500);
}
