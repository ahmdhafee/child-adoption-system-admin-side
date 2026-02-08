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
  $rows = $pdo->query("
    SELECT id, client_name, client_email, subject, message, type, status, priority, created_at
    FROM inquiries
    ORDER BY created_at DESC
  ")->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['success'=>true,'inquiries'=>$rows]);
} catch (PDOException $e) {
  error_log("get_inquiries admin error: ".$e->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Server error']);
}
