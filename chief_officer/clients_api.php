<?php
declare(strict_types=1);

require_once '../officer_auth.php';
require_once '../officer_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'chief') {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Access denied']);
    exit;
}

function ok(array $data=[]): void { echo json_encode(array_merge(['success'=>true],$data)); exit; }
function fail(string $m,int $c=400): void { http_response_code($c); echo json_encode(['success'=>false,'message'=>$m]); exit; }

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {
    if ($action === 'list') {
        $stmt = $pdo->query("
            SELECT
              u.id AS user_id,
              u.email,
              u.registration_id,
              u.status AS user_status,
              u.created_at,
              u.last_login,
              a.partner1_name,
              a.partner2_name,
              a.eligibility_score,
              a.status AS application_status
            FROM users u
            LEFT JOIN applications a ON a.user_id = u.id
            ORDER BY u.created_at DESC
        ");

        $clients = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $name = trim(($r['partner1_name'] ?? '') . ' & ' . ($r['partner2_name'] ?? ''));
            if ($name === '' || $name === '&') $name = $r['email'];

            // document counts
            $docStmt = $pdo->prepare("SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) AS approved
              FROM documents
              WHERE user_id = ?
            ");
            $docStmt->execute([(int)$r['user_id']]);
            $doc = $docStmt->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'approved'=>0];

            // voted?
            $voteStmt = $pdo->prepare("SELECT COUNT(*) FROM user_votes WHERE user_id = ? AND status='active'");
            $voteStmt->execute([(int)$r['user_id']]);
            $hasVoted = ((int)$voteStmt->fetchColumn() > 0);

            // appointments count
            $appStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ?");
            $appStmt->execute([(int)$r['user_id']]);
            $apptCount = (int)$appStmt->fetchColumn();

            $clients[] = [
                'user_id' => (int)$r['user_id'],
                'email' => $r['email'],
                'registration_id' => $r['registration_id'],
                'name' => $name,
                'user_status' => $r['user_status'] ?? 'pending',
                'eligibility_score' => (int)($r['eligibility_score'] ?? 0),
                'application_status' => $r['application_status'] ?? 'pending',
                'docs_total' => (int)($doc['total'] ?? 0),
                'docs_approved' => (int)($doc['approved'] ?? 0),
                'has_voted' => $hasVoted,
                'appointment_count' => $apptCount,
            ];
        }

        ok(['clients'=>$clients]);
    }

    if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        if ($user_id <= 0) fail('Invalid user');

        $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$user_id]);
        $cur = $stmt->fetchColumn();
        if (!$cur) fail('User not found');

        $new = ($cur === 'active') ? 'suspended' : 'active';
        $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$new, $user_id]);

        ok(['message'=>"Client status updated to $new"]);
    }

    fail('Unknown action');

} catch (PDOException $e) {
    error_log("clients_api error: " . $e->getMessage());
    fail('Server error', 500);
}
