<?php
declare(strict_types=1);
require_once '../../officer_auth.php';
require_once '../../officer_db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'chief') {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Access denied']);
    exit;
}

$stmt = $pdo->query("SELECT id, section_key, title, category, content, status, updated_at FROM guidelines ORDER BY category, section_key");
echo json_encode(['success'=>true,'items'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
