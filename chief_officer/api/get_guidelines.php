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

/*
  We use category-based fetching (Option A):
  categories: eligibility, application, legal, child-welfare, international
*/
$category = trim($_GET['category'] ?? '');
if ($category === '') {
    echo json_encode(['success'=>false,'message'=>'Missing category']);
    exit;
}

$stmt = $pdo->prepare("
  SELECT id, title, category, content, status, updated_at
  FROM guidelines
  WHERE category = ?
  ORDER BY updated_at DESC
  LIMIT 1
");
$stmt->execute([$category]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(['success'=>false,'message'=>'Guideline not found']);
    exit;
}

echo json_encode(['success'=>true,'item'=>$row]);
