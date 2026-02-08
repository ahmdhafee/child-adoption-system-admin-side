<?php
declare(strict_types=1);

require_once '../../officer_auth.php';
require_once '../../officer_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['success'=>false,'message'=>'Access denied']);
  exit;
}

$id = (int)($_POST['id'] ?? 0);
$response = trim($_POST['response'] ?? '');
$status = trim($_POST['status'] ?? '');
$priority = trim($_POST['priority'] ?? '');

if ($id <= 0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid inquiry id']); exit; }
if ($response === '') { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Response is required']); exit; }

$allowedStatus = ['new','pending','resolved','inprogress']; // accept inprogress too
$allowedPriority = ['low','medium','high'];

if (!in_array($status, $allowedStatus, true)) $status = 'pending';
if ($status === 'inprogress') $status = 'pending'; // normalize
if (!in_array($priority, $allowedPriority, true)) $priority = 'medium';

try {
  $stmt = $pdo->prepare("
    UPDATE inquiries
    SET reply_message = ?, status = ?, priority = ?, replied_at = NOW()
    WHERE id = ?
  ");
  $stmt->execute([$response, $status, $priority, $id]);

  echo json_encode(['success'=>true,'message'=>'Reply saved successfully']);
} catch (PDOException $e) {
  error_log("reply_inquiry admin error: ".$e->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Server error']);
}
