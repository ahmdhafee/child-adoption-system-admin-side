<?php
require_once '../../officer_auth.php';
require_once '../../officer_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'chief') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$stmt = $pdo->query("
    SELECT id, client_name, client_email, subject, message, type, priority, status, created_at
    FROM inquiries
    ORDER BY created_at DESC
");

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
