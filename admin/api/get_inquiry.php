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

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['success'=>false,'message'=>'Invalid inquiry id']);
  exit;
}

try {
  $stmt = $pdo->prepare("
    SELECT id, client_name, client_email, subject, message, type, status, priority, reply_message, created_at, replied_at
    FROM inquiries
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->execute([$id]);
  $inq = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$inq) {
    http_response_code(404);
    echo json_encode(['success'=>false,'message'=>'Inquiry not found']);
    exit;
  }

  echo json_encode(['success'=>true,'inquiry'=>$inq]);
} catch (PDOException $e) {
  error_log("get_inquiry admin error: ".$e->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Server error']);
}
