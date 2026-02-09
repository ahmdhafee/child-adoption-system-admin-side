<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/officer_db.php'; // must create $pdo

function fail(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

function ok(array $data = []): void {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Invalid request', 405);
}

if (!isset($_SESSION['pending_officer_id'])) {
    fail('Session expired. Login again.', 401);
}

$otp = trim($_POST['otp'] ?? '');
if ($otp === '' || !ctype_digit($otp) || strlen($otp) !== 6) {
    fail('Enter a valid 6-digit OTP');
}

$officerId = (int)$_SESSION['pending_officer_id'];

try {
    
    $stmt = $pdo->prepare("
        SELECT id, otp_hash, expires_at, is_used
        FROM officer_otp
        WHERE officer_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->execute([$officerId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        fail('OTP not found. Please login again.');
    }

    if ((int)$row['is_used'] === 1) {
        fail('OTP already used. Please login again.');
    }

    if (strtotime((string)$row['expires_at']) < time()) {
        fail('OTP expired. Please login again.');
    }

    if (!password_verify($otp, (string)$row['otp_hash'])) {
        fail('Invalid OTP');
    }

    
    $pdo->prepare("UPDATE officer_otp SET is_used = 1 WHERE id = ?")
        ->execute([(int)$row['id']]);

    
    $stmt2 = $pdo->prepare("
        SELECT id, email, full_name, role, status
        FROM officers
        WHERE id = ?
        LIMIT 1
    ");
    $stmt2->execute([$officerId]);
    $officer = $stmt2->fetch(PDO::FETCH_ASSOC);

    if (!$officer) {
        fail('Officer account not found.');
    }

    if ($officer['status'] !== 'active') {
        fail('Account not active.');
    }

    
    session_regenerate_id(true);

    $_SESSION['officer_id'] = (int)$officer['id'];
    $_SESSION['officer_role'] = $officer['role']; 
    $_SESSION['officer_email'] = $officer['email'];
    $_SESSION['officer_name'] = $officer['full_name'];
    $_SESSION['officer_login_time'] = time();

    
    unset($_SESSION['pending_officer_id'], $_SESSION['pending_officer_email']);


    $pdo->prepare("UPDATE officers SET last_login = NOW() WHERE id = ?")
        ->execute([(int)$officer['id']]);

   
    $redirect = ($officer['role'] === 'chief') ? 'chief_officer/index.php' : 'admin/index.php';

    ok([
        'redirect' => $redirect,
        'message'  => 'OTP verified. Login success'
    ]);

} catch (PDOException $e) {
    error_log("verify_officer_otp_handler error: " . $e->getMessage());
    fail('Server error. Please try again later.', 500);
}
