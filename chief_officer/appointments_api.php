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
        $rows = $pdo->query("
            SELECT a.*,
                   u.email AS client_email
            FROM appointments a
            LEFT JOIN users u ON u.id = a.user_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $total = count($rows);
        $upcoming = 0; $completed = 0; $cancelled = 0;
        foreach ($rows as $r) {
            if ($r['status'] === 'upcoming' || $r['status'] === 'scheduled') $upcoming++;
            if ($r['status'] === 'completed') $completed++;
            if ($r['status'] === 'cancelled') $cancelled++;
        }

        ok([
            'appointments' => $rows,
            'stats' => [
                'total' => $total,
                'upcoming' => $upcoming,
                'completed' => $completed,
                'cancelled' => $cancelled
            ]
        ]);
    }

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $appointment_type = trim($_POST['appointment_type'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $appointment_date = trim($_POST['appointment_date'] ?? '');
        $appointment_time = trim($_POST['appointment_time'] ?? '');
        $duration = trim($_POST['duration'] ?? '1 hour');
        $meeting_location = trim($_POST['meeting_location'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $appointment_notes = trim($_POST['appointment_notes'] ?? '');
        $status = trim($_POST['status'] ?? 'scheduled');
        $confirmed = (int)($_POST['confirmed'] ?? 0);

        if ($user_id <= 0 || $appointment_type === '' || $appointment_date === '' || $appointment_time === '' || $meeting_location === '') {
            fail('Missing required fields');
        }

        $stmt = $pdo->prepare("
            INSERT INTO appointments
            (user_id, appointment_type, title, appointment_date, appointment_time, duration, status, meeting_location, address, appointment_notes, confirmed)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id, $appointment_type, $title,
            $appointment_date, $appointment_time, $duration,
            $status, $meeting_location, $address, $appointment_notes, $confirmed
        ]);

        ok(['message'=>'Appointment created successfully']);
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) fail('Invalid id');

        $user_id = (int)($_POST['user_id'] ?? 0);
        $appointment_type = trim($_POST['appointment_type'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $appointment_date = trim($_POST['appointment_date'] ?? '');
        $appointment_time = trim($_POST['appointment_time'] ?? '');
        $duration = trim($_POST['duration'] ?? '1 hour');
        $meeting_location = trim($_POST['meeting_location'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $appointment_notes = trim($_POST['appointment_notes'] ?? '');
        $status = trim($_POST['status'] ?? 'scheduled');
        $confirmed = (int)($_POST['confirmed'] ?? 0);

        if ($user_id <= 0 || $appointment_type === '' || $appointment_date === '' || $appointment_time === '' || $meeting_location === '') {
            fail('Missing required fields');
        }

        $stmt = $pdo->prepare("
            UPDATE appointments
            SET user_id=?, appointment_type=?, title=?, appointment_date=?, appointment_time=?,
                duration=?, status=?, meeting_location=?, address=?, appointment_notes=?, confirmed=?
            WHERE id=?
        ");
        $stmt->execute([
            $user_id, $appointment_type, $title,
            $appointment_date, $appointment_time,
            $duration, $status, $meeting_location, $address, $appointment_notes, $confirmed,
            $id
        ]);

        ok(['message'=>'Appointment updated successfully']);
    }

    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) fail('Invalid id');

        $pdo->prepare("DELETE FROM appointments WHERE id=?")->execute([$id]);
        ok(['message'=>'Appointment deleted successfully']);
    }

    fail('Unknown action');

} catch (PDOException $e) {
    error_log("appointments_api error: " . $e->getMessage());
    fail('Server error', 500);
}
