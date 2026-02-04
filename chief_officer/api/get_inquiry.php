<?php
require_once '../../officer_auth.php';
require_once '../../officer_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'chief') {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Access denied']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success'=>false,'message'=>'Invalid inquiry id']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, client_name, client_email, subject, message, type, priority, status, created_at, reply_message
    FROM inquiries
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(['success'=>false,'message'=>'Inquiry not found']);
    exit;
}

/* Mark as read (only if column exists) */
try {
    $pdo->prepare("UPDATE inquiries SET is_read = 1 WHERE id = ?")->execute([$id]);
} catch (Exception $e) {
    // ignore if is_read not added
}

echo json_encode(['success'=>true,'inquiry'=>$row]);
