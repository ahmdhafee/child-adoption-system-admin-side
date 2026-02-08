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

function cleanCsv(string $s): string {
    // keep commas, remove weird spaces
    $s = trim($s);
    $s = preg_replace('/\s+/', '', $s);
    return $s ?? '';
}

try {

    /* ========= LIST ========= */
    if ($action === 'list') {

        // We support both schemas:
        // - required_documents table may have is_active + updated_at
        // If not present, query will fail. So keep it simple + safe:
        $rows = $pdo->query("
            SELECT
              id,
              requirement_name,
              category,
              description,
              is_required,
              max_size_mb,
              allowed_formats,
              sort_order,
              COALESCE(is_active, 1) AS is_active,
              created_at,
              COALESCE(updated_at, created_at) AS updated_at
            FROM required_documents
            ORDER BY sort_order ASC, id ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        ok(['required_documents' => $rows]);
    }

    /* ========= CREATE ========= */
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['requirement_name'] ?? '');
        $cat  = trim($_POST['category'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $isRequired = (int)($_POST['is_required'] ?? 1);
        $maxSize = (int)($_POST['max_size_mb'] ?? 10);
        $formats = cleanCsv((string)($_POST['allowed_formats'] ?? 'pdf,jpg,jpeg,png'));
        $isActive = (int)($_POST['is_active'] ?? 1);

        if ($name === '' || $cat === '' || $maxSize <= 0 || $formats === '') {
            fail('Missing required fields');
        }

        // Next sort order = max + 1
        $max = (int)$pdo->query("SELECT COALESCE(MAX(sort_order),0) FROM required_documents")->fetchColumn();
        $sort = $max + 1;

        // Works if table has is_active + updated_at. If your table doesn't, add columns (recommended).
        $stmt = $pdo->prepare("
            INSERT INTO required_documents
              (requirement_name, category, description, is_required, max_size_mb, allowed_formats, sort_order, is_active, created_at, updated_at)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$name, $cat, $desc, $isRequired, $maxSize, $formats, $sort, $isActive]);

        ok(['message' => 'Requirement created']);
    }

    /* ========= UPDATE ========= */
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) fail('Invalid id');

        $name = trim($_POST['requirement_name'] ?? '');
        $cat  = trim($_POST['category'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $isRequired = (int)($_POST['is_required'] ?? 1);
        $maxSize = (int)($_POST['max_size_mb'] ?? 10);
        $formats = cleanCsv((string)($_POST['allowed_formats'] ?? 'pdf,jpg,jpeg,png'));
        $isActive = (int)($_POST['is_active'] ?? 1);

        if ($name === '' || $cat === '' || $maxSize <= 0 || $formats === '') {
            fail('Missing required fields');
        }

        $stmt = $pdo->prepare("
            UPDATE required_documents
            SET requirement_name=?,
                category=?,
                description=?,
                is_required=?,
                max_size_mb=?,
                allowed_formats=?,
                is_active=?,
                updated_at=NOW()
            WHERE id=?
        ");
        $stmt->execute([$name, $cat, $desc, $isRequired, $maxSize, $formats, $isActive, $id]);

        ok(['message' => 'Requirement updated']);
    }

    /* ========= TOGGLE ACTIVE ========= */
    if ($action === 'toggle_active' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) fail('Invalid id');

        $stmt = $pdo->prepare("
            UPDATE required_documents
            SET is_active = CASE WHEN COALESCE(is_active,1)=1 THEN 0 ELSE 1 END,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$id]);

        ok(['message' => 'Active status changed']);
    }

    /* ========= REORDER (swap sort_order with neighbor) ========= */
    if ($action === 'reorder' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $dir = trim($_POST['dir'] ?? ''); // up/down
        if ($id <= 0) fail('Invalid id');
        if ($dir !== 'up' && $dir !== 'down') fail('Invalid direction');

        // Current
        $stmt = $pdo->prepare("SELECT id, sort_order FROM required_documents WHERE id=?");
        $stmt->execute([$id]);
        $cur = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cur) fail('Not found', 404);

        $curOrder = (int)$cur['sort_order'];

        // Neighbor
        if ($dir === 'up') {
            $stmt = $pdo->prepare("
                SELECT id, sort_order
                FROM required_documents
                WHERE sort_order < ?
                ORDER BY sort_order DESC
                LIMIT 1
            ");
            $stmt->execute([$curOrder]);
        } else {
            $stmt = $pdo->prepare("
                SELECT id, sort_order
                FROM required_documents
                WHERE sort_order > ?
                ORDER BY sort_order ASC
                LIMIT 1
            ");
            $stmt->execute([$curOrder]);
        }

        $nb = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$nb) ok(['message' => 'No change']); // already top/bottom

        $pdo->beginTransaction();

        // swap
        $stmt = $pdo->prepare("UPDATE required_documents SET sort_order=? WHERE id=?");
        $stmt->execute([(int)$nb['sort_order'], $id]);
        $stmt->execute([$curOrder, (int)$nb['id']]);

        // touch updated_at
        $pdo->prepare("UPDATE required_documents SET updated_at=NOW() WHERE id IN (?,?)")
            ->execute([$id, (int)$nb['id']]);

        $pdo->commit();

        ok(['message' => 'Order updated']);
    }

    /* ========= DELETE ========= */
    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) fail('Invalid id');

        $pdo->prepare("DELETE FROM required_documents WHERE id=?")->execute([$id]);

        ok(['message' => 'Requirement deleted']);
    }

    fail('Unknown action');

} catch (PDOException $e) {
    error_log("required_documents_api error: " . $e->getMessage());
    fail('Server error', 500);
}
