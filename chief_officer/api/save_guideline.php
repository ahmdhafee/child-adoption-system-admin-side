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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Invalid method']);
    exit;
}

$section_key = trim($_POST['section_key'] ?? '');
$title = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? '');
$content = trim($_POST['content'] ?? '');
$status = trim($_POST['status'] ?? 'active');
$chiefId = (int)($_SESSION['officer_id'] ?? 0);

if ($section_key === '' || $title === '' || $category === '' || $content === '') {
    echo json_encode(['success'=>false,'message'=>'All fields are required']);
    exit;
}

$allowedCat = ['eligibility','application','legal','child-welfare','international'];
$allowedStatus = ['active','draft','archived'];

if (!in_array($category, $allowedCat, true)) $category = 'eligibility';
if (!in_array($status, $allowedStatus, true)) $status = 'active';

try {
    $stmt = $pdo->prepare("
        INSERT INTO guidelines (section_key, title, category, content, status, updated_by)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
          title = VALUES(title),
          category = VALUES(category),
          content = VALUES(content),
          status = VALUES(status),
          updated_by = VALUES(updated_by),
          updated_at = NOW()
    ");
    $stmt->execute([$section_key, $title, $category, $content, $status, $chiefId]);

    echo json_encode(['success'=>true,'message'=>'Guideline saved successfully']);
} catch (Exception $e) {
    error_log("save_guideline error: ".$e->getMessage());
    echo json_encode(['success'=>false,'message'=>'Database error']);
}
