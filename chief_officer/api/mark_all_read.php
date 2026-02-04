<?php
require_once '../../officer_auth.php';
require_once '../../officer_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'chief') {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Access denied']);
    exit;
}

try {
    $pdo->query("UPDATE inquiries SET is_read = 1");
    echo json_encode(['success'=>true,'message'=>'All inquiries marked as read']);
} catch (Exception $e) {
    echo json_encode([
        'success'=>false,
        'message'=>'is_read column not found. Please add it in DB.'
    ]);
}
