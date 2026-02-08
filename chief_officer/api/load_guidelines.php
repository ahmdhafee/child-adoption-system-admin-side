<?php
declare(strict_types=1);

require_once '../../officer_auth.php';
require_once '../../officer_db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'chief') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    // guidelines table columns: id, section_key, title, description, content_json, status, updated_by, updated_at, created_at
    $stmt = $pdo->query("
        SELECT id, section_key, title, description, content_json, status, updated_at
        FROM guidelines
        ORDER BY section_key ASC
    ");

    echo json_encode([
        'success' => true,
        'items' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}
