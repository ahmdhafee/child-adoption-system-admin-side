<?php
// admin/documents_review_api.php
require_once '../officer_auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'admin') {
  echo json_encode(['success' => false, 'message' => 'Access denied']);
  exit;
}

function out($arr){ echo json_encode($arr); exit; }
function intv($v){ return (int)($v ?? 0); }

// DB
$host = 'localhost';
$dbname = 'family_bridge';
$username = 'root';
$password = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);
} catch (Throwable $e) {
  out(['success' => false, 'message' => 'DB connection failed']);
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {

  // ✅ 1) GET clients list (users who have documents)
  if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'clients') {
    $sql = "
      SELECT DISTINCT u.id, u.email
      FROM users u
      INNER JOIN documents d ON d.user_id = u.id
      ORDER BY u.id DESC
      LIMIT 500
    ";
    $clients = $pdo->query($sql)->fetchAll();
    out(['success' => true, 'clients' => $clients]);
  }

  // ✅ 2) GET list documents for a client
  if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $user_id = intv($_GET['user_id'] ?? 0);
    if ($user_id <= 0) out(['success' => false, 'message' => 'Invalid user_id']);

    // requirement_name is not available in your DB (no required_documents table)
    // So we send requirement_name as "REQ-<id>" if requirement_id exists
    $sql = "
      SELECT
        d.id,
        d.user_id,
        d.requirement_id,
        d.file_name,
        d.original_name,
        d.file_path,
        d.file_size,
        d.file_type,
        d.category,
        d.description,
        d.status,
        d.review_notes,
        d.review_date,
        d.upload_date,
        IFNULL(CONCAT('REQ-', d.requirement_id), '-') AS requirement_name
      FROM documents d
      WHERE d.user_id = ?
      ORDER BY d.id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $docs = $stmt->fetchAll();

    out(['success' => true, 'documents' => $docs]);
  }

  // ✅ 3) GET single document details
  if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get') {
    $id = intv($_GET['id'] ?? 0);
    if ($id <= 0) out(['success' => false, 'message' => 'Invalid document id']);

    $sql = "
      SELECT
        d.*,
        u.email AS client_email,
        IFNULL(CONCAT('REQ-', d.requirement_id), '-') AS requirement_name
      FROM documents d
      LEFT JOIN users u ON u.id = d.user_id
      WHERE d.id = ?
      LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $doc = $stmt->fetch();

    if (!$doc) out(['success' => false, 'message' => 'Document not found']);
    out(['success' => true, 'document' => $doc]);
  }

  // ✅ 4) POST approve/reject
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'approve' || $action === 'reject')) {

    $id = intv($_POST['id'] ?? 0);
    $notes = trim((string)($_POST['review_notes'] ?? ''));

    if ($id <= 0) out(['success' => false, 'message' => 'Invalid document id']);
    if ($action === 'reject' && $notes === '') {
      out(['success' => false, 'message' => 'Please add reason in review notes before rejecting']);
    }

    $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

    // officer id if you store it in session (optional)
    $reviewed_by = isset($_SESSION['officer_id']) ? (int)$_SESSION['officer_id'] : null;

    $stmt = $pdo->prepare("
      UPDATE documents
      SET
        status = ?,
        review_notes = ?,
        reviewed_by = ?,
        review_date = NOW()
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->execute([
      $newStatus,
      ($notes !== '' ? $notes : null),
      $reviewed_by,
      $id
    ]);

    out(['success' => true, 'message' => "Document {$newStatus}"]);
  }

  out(['success' => false, 'message' => 'Invalid action']);

} catch (Throwable $e) {
  error_log("documents_review_api.php error: " . $e->getMessage());
  out(['success' => false, 'message' => 'Server error']);
}


