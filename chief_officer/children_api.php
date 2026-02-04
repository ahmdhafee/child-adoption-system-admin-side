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

function ok(array $data=[]): void { echo json_encode(array_merge(['success'=>true],$data)); exit; }
function fail(string $m,int $c=400): void { http_response_code($c); echo json_encode(['success'=>false,'message'=>$m]); exit; }

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

function calcAge(string $dob): int {
    $d = new DateTime($dob);
    $now = new DateTime();
    return (int)$now->diff($d)->y;
}

function nextChildCode(PDO $pdo): string {
    $year = date('Y');
    $prefix = "CH-$year-";
    $stmt = $pdo->prepare("SELECT child_code FROM children WHERE child_code LIKE ? ORDER BY child_code DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    if (!$last) return $prefix . "001";
    $num = (int)substr((string)$last, strlen($prefix));
    $num++;
    return $prefix . str_pad((string)$num, 3, '0', STR_PAD_LEFT);
}

try {
    if ($action === 'list') {
        $rows = $pdo->query("SELECT * FROM children ORDER BY added_at DESC")->fetchAll(PDO::FETCH_ASSOC);

        $total = count($rows);
        $available = 0; $pending = 0; $adopted = 0;
        foreach ($rows as $r) {
            if ($r['status'] === 'available') $available++;
            if ($r['status'] === 'processing' || $r['status'] === 'pending' || $r['status'] === 'matched') $pending++;
            if ($r['status'] === 'adopted') $adopted++;
        }

        ok([
            'children' => $rows,
            'stats' => [
                'total_children' => $total,
                'available_children' => $available,
                'pending_children' => $pending,
                'adopted_children' => $adopted,
            ]
        ]);
    }

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName  = trim($_POST['lastName'] ?? '');
        $dob       = trim($_POST['dob'] ?? '');
        $gender    = trim($_POST['gender'] ?? '');
        $status    = trim($_POST['status'] ?? 'available');
        $dateReg   = trim($_POST['dateRegistered'] ?? '');
        $medical   = trim($_POST['medicalHistory'] ?? '');
        $notes     = trim($_POST['specialNotes'] ?? '');

        if ($firstName==='' || $lastName==='' || $dob==='' || $gender==='' || $dateReg==='') {
            fail('Missing required fields');
        }

        // Your DB status enum is: available, matched, processing, adopted
        // Your UI uses: available, reserved, pending, adopted
        // We map them safely:
        $mappedStatus = match($status) {
            'available' => 'available',
            'adopted'   => 'adopted',
            'pending'   => 'processing',
            'reserved'  => 'matched',
            default     => 'available'
        };

        $childCode = nextChildCode($pdo);
        $name = $firstName . ' ' . $lastName;
        $age = calcAge($dob);

        $stmt = $pdo->prepare("
            INSERT INTO children (child_code, name, age, gender, date_of_birth, health_status, special_needs, status, added_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $childCode,
            $name,
            $age,
            $gender,
            $dob,
            $medical,
            $notes,
            $mappedStatus,
            $dateReg . " 00:00:00"
        ]);

        ok(['message'=>'Child added successfully']);
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id        = (int)($_POST['id'] ?? 0);
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName  = trim($_POST['lastName'] ?? '');
        $dob       = trim($_POST['dob'] ?? '');
        $gender    = trim($_POST['gender'] ?? '');
        $status    = trim($_POST['status'] ?? 'available');
        $dateReg   = trim($_POST['dateRegistered'] ?? '');
        $medical   = trim($_POST['medicalHistory'] ?? '');
        $notes     = trim($_POST['specialNotes'] ?? '');

        if ($id<=0) fail('Invalid child id');
        if ($firstName==='' || $lastName==='' || $dob==='' || $gender==='' || $dateReg==='') {
            fail('Missing required fields');
        }

        $mappedStatus = match($status) {
            'available' => 'available',
            'adopted'   => 'adopted',
            'pending'   => 'processing',
            'reserved'  => 'matched',
            default     => 'available'
        };

        $name = $firstName . ' ' . $lastName;
        $age = calcAge($dob);

        $stmt = $pdo->prepare("
            UPDATE children
            SET name = ?, age = ?, gender = ?, date_of_birth = ?, health_status = ?, special_needs = ?, status = ?, added_at = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $name,
            $age,
            $gender,
            $dob,
            $medical,
            $notes,
            $mappedStatus,
            $dateReg . " 00:00:00",
            $id
        ]);

        ok(['message'=>'Child updated successfully']);
    }

    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id<=0) fail('Invalid child id');
        $pdo->prepare("DELETE FROM children WHERE id = ?")->execute([$id]);
        ok(['message'=>'Child deleted successfully']);
    }

    fail('Unknown action');

} catch (PDOException $e) {
    error_log("children_api error: " . $e->getMessage());
    fail('Server error', 500);
}
