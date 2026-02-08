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

    /* ✅ CLIENTS DROPDOWN */
    if ($action === 'clients') {
        // You can adjust this query if you want only "approved clients" etc.
        // Now: shows users who have at least 1 application OR at least 1 document (more useful)
        $stmt = $pdo->query("
            SELECT DISTINCT u.id, u.email, u.registration_id
            FROM users u
            LEFT JOIN applications a ON a.user_id = u.id
            LEFT JOIN documents d ON d.user_id = u.id
            WHERE (a.id IS NOT NULL OR d.id IS NOT NULL)
            ORDER BY u.id DESC
        ");
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ok(['clients' => $clients]);
    }

    /* ✅ LIST (OPTIONAL FILTER BY USER) */
    if ($action === 'list') {

        $userId = (int)($_GET['user_id'] ?? 0);

        if ($userId > 0) {
            $stmt = $pdo->prepare("
                SELECT
                  d.id,
                  d.user_id,
                  d.requirement_id,
                  d.file_name,
                  d.original_name,
                  d.file_path,
                  d.file_size,
                  d.category,
                  d.status,
                  d.review_notes,
                  d.upload_date,
                  u.email AS client_email,
                  rd.requirement_name
                FROM documents d
                LEFT JOIN users u ON u.id = d.user_id
                LEFT JOIN required_documents rd ON rd.id = d.requirement_id
                WHERE d.user_id = ?
                ORDER BY d.upload_date DESC, d.id DESC
            ");
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            ok(['documents' => $rows]);
        }

        // If no user_id is provided, keep old behavior: list all
        $rows = $pdo->query("
            SELECT
              d.id,
              d.user_id,
              d.requirement_id,
              d.file_name,
              d.original_name,
              d.file_path,
              d.file_size,
              d.category,
              d.status,
              d.review_notes,
              d.upload_date,
              u.email AS client_email,
              rd.requirement_name
            FROM documents d
            LEFT JOIN users u ON u.id = d.user_id
            LEFT JOIN required_documents rd ON rd.id = d.requirement_id
            ORDER BY d.upload_date DESC, d.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        ok(['documents'=>$rows]);
    }

    /* ✅ GET ONE */
    if ($action === 'get') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) fail('Invalid document id');

        $stmt = $pdo->prepare("
            SELECT
              d.*,
              u.email AS client_email,
              rd.requirement_name
            FROM documents d
            LEFT JOIN users u ON u.id = d.user_id
            LEFT JOIN required_documents rd ON rd.id = d.requirement_id
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) fail('Document not found', 404);

        ok(['document'=>$row]);
    }

    /* ✅ APPROVE */
    if ($action === 'approve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $notes = trim($_POST['review_notes'] ?? '');

        if ($id <= 0) fail('Invalid document id');

        $stmt = $pdo->prepare("
            UPDATE documents
            SET status='approved',
                review_notes=?,
                reviewed_by=?,
                review_date=NOW()
            WHERE id=?
        ");
        $stmt->execute([$notes, (int)($_SESSION['officer_id'] ?? 0), $id]);

        ok(['message'=>'Document approved']);
    }

    /* ✅ REJECT */
    if ($action === 'reject' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $notes = trim($_POST['review_notes'] ?? '');

        if ($id <= 0) fail('Invalid document id');
        if ($notes === '') fail('Review notes required for rejection');

        $stmt = $pdo->prepare("
            UPDATE documents
            SET status='rejected',
                review_notes=?,
                reviewed_by=?,
                review_date=NOW()
            WHERE id=?
        ");
        $stmt->execute([$notes, (int)($_SESSION['officer_id'] ?? 0), $id]);

        ok(['message'=>'Document rejected']);
    }

    fail('Unknown action');

} catch (PDOException $e) {
    error_log("documents_review_api error: " . $e->getMessage());
    fail('Server error', 500);
}
