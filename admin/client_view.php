<?php
require_once '../officer_auth.php';
require_once '../officer_db.php';

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'admin') {
    die('Access denied');
}

$user_id = (int)($_GET['user_id'] ?? 0);
if ($user_id <= 0) die('Invalid user_id');

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$u) die('Client not found');
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Client View</title>
</head>
<body style="font-family:Segoe UI; padding:20px;">
  <h2>Client Profile</h2>
  <p><b>Name:</b> <?php echo htmlspecialchars(($u['first_name']??'').' '.($u['last_name']??'')); ?></p>
  <p><b>Email:</b> <?php echo htmlspecialchars($u['email']??'-'); ?></p>
  <p><b>Registration ID:</b> <?php echo htmlspecialchars($u['registration_id']??'-'); ?></p>
  <p><b>Status:</b> <?php echo htmlspecialchars($u['status']??'-'); ?></p>
  <p><b>Application Status:</b> <?php echo htmlspecialchars($u['application_status']??'-'); ?></p>
</body>
</html>
