<?php
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

function cleanStatus(string $status): string {
    // Keep your UI statuses
    $allowed = ['scheduled', 'upcoming', 'completed', 'cancelled'];
    return in_array($status, $allowed, true) ? $status : 'scheduled';
}

try {
    if ($action === 'list') {
        $rows = $pdo->query("
            SELECT a.*, u.email AS client_email
            FROM appointments a
            LEFT JOIN users u ON u.id = a.user_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $total = count($rows);
        $upcoming = 0;
        $completed = 0;
        $cancelled = 0;

        foreach ($rows as $r) {
            $st = (string)($r['status'] ?? '');
            if ($st === 'upcoming' || $st === 'scheduled') $upcoming++;
            if ($st === 'completed') $completed++;
            if ($st === 'cancelled') $cancelled++;
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
        $appointment_type = trim((string)($_POST['appointment_type'] ?? ''));
        $title = trim((string)($_POST['title'] ?? ''));
        $appointment_date = trim((string)($_POST['appointment_date'] ?? ''));
        $appointment_time = trim((string)($_POST['appointment_time'] ?? ''));
        $duration = trim((string)($_POST['duration'] ?? '1 hour'));
        $meeting_location = trim((string)($_POST['meeting_location'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));
        $appointment_notes = trim((string)($_POST['appointment_notes'] ?? ''));
        $status = cleanStatus(trim((string)($_POST['status'] ?? 'scheduled')));
        $confirmed = (int)($_POST['confirmed'] ?? 0);

        if ($user_id <= 0 || $appointment_type === '' || $appointment_date === '' || $appointment_time === '' || $meeting_location === '') {
            fail('Missing required fields');
        }

        // Check if the client has voted for a child
        $voteCheck = $pdo->prepare("SELECT COUNT(*) FROM user_votes WHERE user_id = ? AND status = 'active'");
        $voteCheck->execute([$user_id]);
        $hasVoted = (int)$voteCheck->fetchColumn();

        if (!$hasVoted) {
            fail('Client must vote for a child before scheduling an appointment');
        }

        // Optional: validate user exists
        $check = $pdo->prepare("SELECT id FROM users WHERE id=? LIMIT 1");
        $check->execute([$user_id]);
        if (!$check->fetchColumn()) {
            fail('Invalid user_id');
        }

        $stmt = $pdo->prepare("
            INSERT INTO appointments
            (user_id, appointment_type, title, appointment_date, appointment_time, duration, status, meeting_location, address, appointment_notes, confirmed)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id, $appointment_type, $title,
            $appointment_date, $appointment_time, $duration,
            $status, $meeting_location, $address, $appointment_notes, $confirmed ? 1 : 0
        ]);

        ok(['message' => 'Appointment created successfully']);
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) fail('Invalid id');

        $user_id = (int)($_POST['user_id'] ?? 0);
        $appointment_type = trim((string)($_POST['appointment_type'] ?? ''));
        $title = trim((string)($_POST['title'] ?? ''));
        $appointment_date = trim((string)($_POST['appointment_date'] ?? ''));
        $appointment_time = trim((string)($_POST['appointment_time'] ?? ''));
        $duration = trim((string)($_POST['duration'] ?? '1 hour'));
        $meeting_location = trim((string)($_POST['meeting_location'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));
        $appointment_notes = trim((string)($_POST['appointment_notes'] ?? ''));
        $status = cleanStatus(trim((string)($_POST['status'] ?? 'scheduled')));
        $confirmed = (int)($_POST['confirmed'] ?? 0);

        if ($user_id <= 0 || $appointment_type === '' || $appointment_date === '' || $appointment_time === '' || $meeting_location === '') {
            fail('Missing required fields');
        }

        // Check if the client has voted for a child
        $voteCheck = $pdo->prepare("SELECT COUNT(*) FROM user_votes WHERE user_id = ? AND status = 'active'");
        $voteCheck->execute([$user_id]);
        $hasVoted = (int)$voteCheck->fetchColumn();

        if (!$hasVoted) {
            fail('Client must vote for a child before scheduling an appointment');
        }

        // Optional: ensure appointment exists
        $exists = $pdo->prepare("SELECT id FROM appointments WHERE id=? LIMIT 1");
        $exists->execute([$id]);
        if (!$exists->fetchColumn()) {
            fail('Appointment not found', 404);
        }

        // Optional: validate user exists
        $check = $pdo->prepare("SELECT id FROM users WHERE id=? LIMIT 1");
        $check->execute([$user_id]);
        if (!$check->fetchColumn()) {
            fail('Invalid user_id');
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
            $duration, $status, $meeting_location, $address, $appointment_notes, $confirmed ? 1 : 0,
            $id
        ]);

        ok(['message' => 'Appointment updated successfully']);
    }

    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) fail('Invalid id');

        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id=?");
        $stmt->execute([$id]);

        ok(['message' => 'Appointment deleted successfully']);
    }

    fail('Unknown action');

} catch (PDOException $e) {
    error_log("appointments_api (admin) error: " . $e->getMessage());
    fail('Server error', 500);
}
