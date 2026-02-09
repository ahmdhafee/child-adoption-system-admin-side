<?php
declare(strict_types=1);

require_once '../officer_auth.php';
require_once '../officer_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

function ok(array $data = []): void {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

function fail(string $m, int $c = 400): void {
    http_response_code($c);
    echo json_encode(['success' => false, 'message' => $m]);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {
    // ✅ LIST ALL CLIENTS (Fetching from applications table)
    if ($action === 'list') {
        // Fetch data from the applications table
        $stmt = $pdo->query("
            SELECT
                a.id AS application_id,
                a.registration_id,
                CONCAT(a.husband_name, ' & ', a.wife_name) AS couple_name,
                a.husband_age,
                a.wife_age,
                a.status AS application_status,
                a.eligibility_score,
                a.created_at
            FROM applications a
            ORDER BY a.id DESC
        ");
        
        if ($stmt === false) {
            throw new Exception('Error executing the SQL query.');
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            fail('No clients found.');
        }

        // Prepare the data for the response
        $clients = array_map(function ($r) {
            return [
                'application_id' => $r['application_id'],
                'registration_id' => $r['registration_id'],
                'couple_name' => $r['couple_name'],
                'husband_age' => $r['husband_age'],
                'wife_age' => $r['wife_age'],
                'application_status' => $r['application_status'],
                'eligibility_score' => $r['eligibility_score'],
                'created_at' => $r['created_at']
            ];
        }, $rows);

        ok(['clients' => $clients]);
    }

    // ✅ LIST CLIENTS WHO HAVE VOTED (Unchanged)
    if ($action === 'list_voted_clients') {
        $stmt = $pdo->query("
            SELECT
                u.id AS user_id,
                u.email,
                CONCAT(u.first_name, ' ', u.last_name) AS client_name
            FROM users u
            JOIN user_votes uv ON u.id = uv.user_id
            WHERE uv.status = 'active'  -- Only clients who have voted
            ORDER BY u.id DESC
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            fail('No clients found who have voted.');
        }

        $clients = array_map(function ($r) {
            return [
                'user_id' => $r['user_id'],
                'name' => $r['client_name'],
                'email' => $r['email'],
            ];
        }, $rows);

        ok(['clients' => $clients]);
    }

    fail('Unknown action');
} catch (PDOException $e) {
    error_log("clients_api error: " . $e->getMessage());
    fail('Server error', 500);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    fail('An unexpected error occurred', 500);
}
?>
