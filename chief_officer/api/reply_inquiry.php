<?php
require_once '../../officer_auth.php';
require_once '../../officer_db.php';
require_once '../mail/send_mail.php';

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

$id = (int)($_POST['id'] ?? 0);
$response = trim($_POST['response'] ?? '');
$status = $_POST['status'] ?? 'inprogress';
$priority = $_POST['priority'] ?? 'medium';

$allowedStatus = ['new','inprogress','resolved'];
$allowedPriority = ['low','medium','high'];

if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }
if ($response === '') { echo json_encode(['success'=>false,'message'=>'Response cannot be empty']); exit; }
if (!in_array($status, $allowedStatus, true)) $status = 'inprogress';
if (!in_array($priority, $allowedPriority, true)) $priority = 'medium';

$officerId = (int)($_SESSION['officer_id'] ?? 0);

// Get inquiry email + subject
$stmt = $pdo->prepare("SELECT client_email, client_name, subject FROM inquiries WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$inq = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$inq) { echo json_encode(['success'=>false,'message'=>'Inquiry not found']); exit; }

// Save reply
$pdo->prepare("
    UPDATE inquiries
    SET reply_message=?, replied_by=?, replied_at=NOW(), status=?, priority=?, is_read=1
    WHERE id=?
")->execute([$response, $officerId, $status, $priority, $id]);

// Send email
$to = $inq['client_email'];
$name = $inq['client_name'] ?? 'Client';
$subject = "Reply: " . ($inq['subject'] ?? 'Your Inquiry');

$body = "Hello $name,\n\n"
      . "We have reviewed your inquiry.\n\n"
      . "Your Message Subject: " . ($inq['subject'] ?? '') . "\n\n"
      . "Our Response:\n$response\n\n"
      . "Regards,\nFamily Bridge - Chief Officer";

$mailResult = sendMail($to, $subject, nl2br(htmlspecialchars($body)));

if (!$mailResult['success']) {
    echo json_encode(['success'=>true,'message'=>'Response saved but email failed: '.$mailResult['message']]);
    exit;
}

echo json_encode(['success'=>true,'message'=>'Response saved & email sent successfully']);
?>