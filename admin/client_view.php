<?php
require_once '../officer_auth.php';
require_once '../officer_db.php';

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'admin') {
    die('Access denied');
}

// Get user_id from the query parameter
$user_id = (int)($_GET['user_id'] ?? 0);
if ($user_id <= 0) die('Invalid user_id');

// Fetch user information
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);
if (!$user) die('Client not found');

// Fetch application details associated with the user
$stmt_app = $pdo->prepare("SELECT * FROM applications WHERE user_id=?");
$stmt_app->execute([$user_id]);
$application = $stmt_app->fetch(PDO::FETCH_ASSOC);
if (!$application) die('Application details not found');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile</title>
</head>
<body style="font-family:Segoe UI; padding:20px;">
    <h2>Client Profile</h2>

    <!-- User Info Section -->
    <h3>User Information</h3>
    <p><b>Name:</b> <?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></p>
    <p><b>Email:</b> <?php echo htmlspecialchars($user['email'] ?? '-'); ?></p>
    <p><b>Registration ID:</b> <?php echo htmlspecialchars($user['registration_id'] ?? '-'); ?></p>
    <p><b>Status:</b> <?php echo htmlspecialchars($user['status'] ?? '-'); ?></p>

    <!-- Application Info Section -->
    <h3>Application Details</h3>
    <p><b>Husband Name:</b> <?php echo htmlspecialchars($application['husband_name'] ?? '-'); ?></p>
    <p><b>Wife Name:</b> <?php echo htmlspecialchars($application['wife_name'] ?? '-'); ?></p>
    <p><b>Marriage Date:</b> <?php echo htmlspecialchars($application['marriage_date'] ?? '-'); ?></p>
    <p><b>Eligibility Score:</b> <?php echo htmlspecialchars($application['eligibility_score'] ?? '-'); ?>%</p>
    <p><b>Application Status:</b> <?php echo htmlspecialchars($application['status'] ?? '-'); ?></p>
    <p><b>Created At:</b> <?php echo htmlspecialchars($application['created_at'] ?? '-'); ?></p>

    <!-- Optionally: Include more application fields like eligibility result, address, income, etc. -->
    <p><b>Eligibility Result:</b> <?php echo htmlspecialchars($application['eligibility_result'] ?? '-'); ?></p>
    <p><b>Eligibility Status:</b> <?php echo htmlspecialchars($application['eligibility_status'] ?? '-'); ?></p>
    <p><b>Monthly Income:</b> <?php echo htmlspecialchars($application['monthly_income'] ?? '-'); ?></p>
</body>
</html>
