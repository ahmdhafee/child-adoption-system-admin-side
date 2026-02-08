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

function nextInstituteCode(PDO $pdo): string {
    $year = date('Y');
    $prefix = "INS-$year-";
    $stmt = $pdo->prepare("SELECT institute_code FROM institutes WHERE institute_code LIKE ? ORDER BY institute_code DESC LIMIT 1");
    $stmt->execute([$prefix.'%']);
    $last = $stmt->fetchColumn();
    if (!$last) return $prefix."001";
    $num = (int)substr((string)$last, strlen($prefix));
    $num++;
    return $prefix . str_pad((string)$num, 3, '0', STR_PAD_LEFT);
}

try {
    if ($action === 'list') {
        $rows = $pdo->query("SELECT * FROM institutes ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

        $total = count($rows);
        $active = 0; $inactive = 0;
        foreach ($rows as $r) {
            if (($r['status'] ?? '') === 'active') $active++;
            else $inactive++;
        }

        ok([
            'institutes' => $rows,
            'stats' => [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ]
        ]);
    }

    if ($action === 'dropdown') {
        // only active institutes for child modal dropdown
        $rows = $pdo->query("SELECT id, institute_code, name FROM institutes WHERE status='active' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        ok(['institutes'=>$rows]);
    }

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') fail('Institute name is required');

        $code = nextInstituteCode($pdo);

        $stmt = $pdo->prepare("
            INSERT INTO institutes (institute_code, name, address, city, contact_person, phone, email, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $code,
            $name,
            trim($_POST['address'] ?? ''),
            trim($_POST['city'] ?? ''),
            trim($_POST['contact_person'] ?? ''),
            trim($_POST['phone'] ?? ''),
            trim($_POST['email'] ?? ''),
            (($_POST['status'] ?? 'active') === 'inactive') ? 'inactive' : 'active'
        ]);

        ok(['message'=>'Institute added successfully']);
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) fail('Invalid institute id');

        $name = trim($_POST['name'] ?? '');
        if ($name === '') fail('Institute name is required');

        $stmt = $pdo->prepare("
            UPDATE institutes
            SET name=?, address=?, city=?, contact_person=?, phone=?, email=?, status=?
            WHERE id=?
        ");
        $stmt->execute([
            $name,
            trim($_POST['address'] ?? ''),
            trim($_POST['city'] ?? ''),
            trim($_POST['contact_person'] ?? ''),
            trim($_POST['phone'] ?? ''),
            trim($_POST['email'] ?? ''),
            (($_POST['status'] ?? 'active') === 'inactive') ? 'inactive' : 'active',
            $id
        ]);

        ok(['message'=>'Institute updated successfully']);
    }

    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) fail('Invalid institute id');

        // If children are linked, FK will set institute_id to NULL
        $pdo->prepare("DELETE FROM institutes WHERE id=?")->execute([$id]);
        ok(['message'=>'Institute deleted successfully']);
    }

    fail('Unknown action');

} catch (PDOException $e) {
    error_log("institutes_api error: ".$e->getMessage());
    fail('Server error', 500);
}
