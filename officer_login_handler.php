<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/officer_db.php';
require_once __DIR__ . '/mailer.php';


function fail(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}
function ok(array $data = []): void {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

function generateOtp(): string {
    return (string)random_int(100000, 999999);
}

function sendOtpDevMode(string $email, string $otp): void {
    // Find OTP in: C:\wamp64\logs\php_error.log
    error_log("OFFICER OTP for $email : $otp");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Invalid request', 405);
}

$role = $_POST['role'] ?? '';
$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    fail('Username/email and password required');
}

if (!in_array($role, ['admin', 'chief'], true)) {
    fail('Invalid role');
}

// Find officer
$stmt = $pdo->prepare("SELECT id, email, full_name, password_hash, role, status
                       FROM officers
                       WHERE email = ? AND role = ?
                       LIMIT 1");
$stmt->execute([$username, $role]);
$officer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$officer) {
    fail('Invalid credentials');
}
if ($officer['status'] !== 'active') {
    fail('Account is suspended');
}
if (!password_verify($password, $officer['password_hash'])) {
    fail('Invalid credentials');
}

// Important: regenerate session
session_regenerate_id(true);

// Create OTP
$otp = generateOtp();
$otpHash = password_hash($otp, PASSWORD_DEFAULT);
$expires = (new DateTime('+5 minutes'))->format('Y-m-d H:i:s');

$pdo->prepare("INSERT INTO officer_otp (officer_id, otp_hash, expires_at) VALUES (?, ?, ?)")
    ->execute([(int)$officer['id'], $otpHash, $expires]);

// Save pending session for OTP verify
$_SESSION['pending_officer_id'] = (int)$officer['id'];
$_SESSION['pending_officer_email'] = $officer['email'];

sendOtpDevMode($officer['email'], $otp);

ok([
    'redirect' => 'verify_officer_otp.php',
    'message' => 'OTP sent. Please verify.'
]);
