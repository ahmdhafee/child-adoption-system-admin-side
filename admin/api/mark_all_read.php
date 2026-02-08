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

try {
  // mark all "new" -> "pending"
  $pdo->exec("UPDATE inquiries SET status='pending' WHERE status='new'");
  echo json_encode(['success'=>true,'message'=>'All inquiries marked as read (pending)']);
} catch (PDOException $e) {
  error_log("mark_all_read admin error: ".$e->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Server error']);
}
