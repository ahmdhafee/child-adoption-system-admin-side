<?php
declare(strict_types=1);

require_once '../officer_auth.php';
require_once '../officer_db.php';

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'chief') {
    http_response_code(403);
    die('Access denied');
}

$user_id = (int)($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
    die('Invalid user_id');
}

$success = '';
$error = '';

// ----- Handle actions -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_application_status') {
            $newStatus = $_POST['app_status'] ?? '';
            $allowed = ['pending','under_review','approved','rejected'];
            if (!in_array($newStatus, $allowed, true)) {
                throw new Exception('Invalid application status');
            }

            $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE user_id = ?");
            $stmt->execute([$newStatus, $user_id]);

            // audit log
            $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, new_value, ip_address, user_agent)
                           VALUES (?, 'update_application_status', 'applications', ?, ?, ?, ?)")
                ->execute([
                    $user_id,
                    $user_id,
                    "status=$newStatus",
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);

            // notify client (optional)
            $pdo->prepare("INSERT INTO user_activities (user_id, activity_type, title, message)
                           VALUES (?, 'info', 'Application Status Updated', ?)")
                ->execute([$user_id, "Your application status has been updated to: $newStatus"]);

            $success = "Application status updated to $newStatus";
        }

        if ($action === 'toggle_user_status') {
            $cur = $pdo->prepare("SELECT status FROM users WHERE id = ? LIMIT 1");
            $cur->execute([$user_id]);
            $currentStatus = (string)$cur->fetchColumn();

            if ($currentStatus === '') throw new Exception('User not found');

            $newStatus = ($currentStatus === 'active') ? 'suspended' : 'active';

            $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$newStatus, $user_id]);

            $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, new_value, ip_address, user_agent)
                           VALUES (?, 'toggle_user_status', 'users', ?, ?, ?, ?)")
                ->execute([
                    $user_id,
                    $user_id,
                    "status=$newStatus",
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);

            $pdo->prepare("INSERT INTO user_activities (user_id, activity_type, title, message)
                           VALUES (?, 'warning', 'Account Status Updated', ?)")
                ->execute([$user_id, "Your account status has been updated to: $newStatus"]);

            $success = "User status updated to $newStatus";
        }

        if ($action === 'add_note') {
            $note = trim($_POST['note'] ?? '');
            if ($note === '') throw new Exception('Note cannot be empty');

            $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, new_value, ip_address, user_agent)
                           VALUES (?, 'chief_note', 'users/applications', ?, ?, ?, ?)")
                ->execute([
                    $user_id,
                    $user_id,
                    $note,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);

            $success = "Note saved to audit log";
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// ----- Fetch client + application -----
$stmt = $pdo->prepare("
    SELECT
      u.id, u.email, u.registration_id, u.status AS user_status, u.created_at, u.last_login,
      a.id AS app_id, a.partner1_name, a.partner1_age, a.partner1_occupation, a.partner1_id, a.partner1_blood_group, a.partner1_medical,
      a.partner2_name, a.partner2_age, a.partner2_occupation, a.partner2_id, a.partner2_blood_group, a.partner2_medical,
      a.district, a.address, a.eligibility_score, a.status AS app_status, a.created_at AS app_created_at
    FROM users u
    LEFT JOIN applications a ON a.user_id = u.id
    WHERE u.id = ?
    LIMIT 1
");
$stmt->execute([$user_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    die('Client not found');
}

// ----- Voting info -----
$voteStmt = $pdo->prepare("
    SELECT uv.vote_date, uv.status AS vote_status, c.child_code, c.name AS child_name
    FROM user_votes uv
    LEFT JOIN children c ON c.id = uv.child_id
    WHERE uv.user_id = ?
    ORDER BY uv.vote_date DESC
    LIMIT 1
");
$voteStmt->execute([$user_id]);
$vote = $voteStmt->fetch(PDO::FETCH_ASSOC);

// ----- Documents -----
$docsStmt = $pdo->prepare("
    SELECT id, original_name, category, status, upload_date, review_date, review_notes
    FROM documents
    WHERE user_id = ?
    ORDER BY upload_date DESC
");
$docsStmt->execute([$user_id]);
$documents = $docsStmt->fetchAll(PDO::FETCH_ASSOC);

$docsTotal = count($documents);
$docsApproved = 0; $docsPending = 0; $docsRejected = 0;
foreach ($documents as $d) {
    if ($d['status'] === 'approved') $docsApproved++;
    elseif ($d['status'] === 'rejected') $docsRejected++;
    else $docsPending++;
}

// ----- Appointments -----
$apptStmt = $pdo->prepare("
    SELECT id, appointment_type, title, appointment_date, appointment_time, status, meeting_location, confirmed
    FROM appointments
    WHERE user_id = ?
    ORDER BY appointment_date DESC, appointment_time DESC
");
$apptStmt->execute([$user_id]);
$appointments = $apptStmt->fetchAll(PDO::FETCH_ASSOC);

// ----- Recent audit logs (for this user) -----
$auditStmt = $pdo->prepare("
    SELECT action, new_value, created_at
    FROM audit_logs
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 10
");
$auditStmt->execute([$user_id]);
$auditLogs = $auditStmt->fetchAll(PDO::FETCH_ASSOC);

function esc($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Client View | Chief Officer</title>
  <link rel="shortcut icon" href="../favlogo.png" type="logo">
  <style>
    body{font-family:Arial;background:#f6f7fb;margin:0;padding:20px;}
    .top{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:15px;}
    .btn{padding:10px 12px;border-radius:10px;border:none;cursor:pointer;font-weight:700}
    .btn-primary{background:#3498db;color:#fff;}
    .btn-danger{background:#e74c3c;color:#fff;}
    .btn-outline{background:#fff;border:1px solid #ddd;}
    .card{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.08);padding:15px;margin-bottom:15px;}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;}
    h2{margin:0 0 10px;}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;font-size:14px;}
    th{background:#f2f4fa;}
    .badge{padding:4px 10px;border-radius:999px;color:#fff;font-size:12px;}
    .active{background:#27ae60;}
    .pending{background:#f39c12;}
    .suspended{background:#e74c3c;}
    .msg{padding:10px;border-radius:10px;margin-bottom:15px;}
    .ok{background:#e8fff1;border:1px solid #b7f0cc;}
    .err{background:#ffecec;border:1px solid #f5b6b6;}
    .mini{font-size:12px;color:#555;}
    textarea,input,select{width:100%;padding:10px;border-radius:10px;border:1px solid #ddd;}
  </style>
</head>
<body>

<div class="top">
  <div>
    <h1 style="margin:0;">Client Profile</h1>
    <div class="mini">User ID: <?php echo esc($client['id']); ?> | Reg ID: <?php echo esc($client['registration_id']); ?></div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a class="btn btn-outline" href="clients.php">← Back to Clients</a>
    <a class="btn btn-outline" href="../officer_logout.php">Logout</a>
  </div>
</div>

<?php if ($success): ?>
  <div class="msg ok"><?php echo esc($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="msg err"><?php echo esc($error); ?></div>
<?php endif; ?>

<div class="grid">
  <div class="card">
    <h2>Client Info</h2>
    <p><b>Email:</b> <?php echo esc($client['email']); ?></p>
    <p><b>Status:</b> <span class="badge <?php echo esc($client['user_status']); ?>"><?php echo esc($client['user_status']); ?></span></p>
    <p><b>Created:</b> <?php echo esc($client['created_at']); ?></p>
    <p><b>Last Login:</b> <?php echo esc($client['last_login'] ?? '-'); ?></p>

    <form method="post" style="margin-top:12px;">
      <input type="hidden" name="action" value="toggle_user_status">
      <button class="btn <?php echo ($client['user_status']==='active') ? 'btn-danger' : 'btn-primary'; ?>" type="submit">
        <?php echo ($client['user_status']==='active') ? 'Suspend User' : 'Activate User'; ?>
      </button>
    </form>
  </div>

  <div class="card">
    <h2>Application</h2>
    <p><b>Eligibility Score:</b> <?php echo (int)($client['eligibility_score'] ?? 0); ?>%</p>
    <p><b>Application Status:</b> <?php echo esc($client['app_status'] ?? 'pending'); ?></p>
    <p><b>Submitted:</b> <?php echo esc($client['app_created_at'] ?? '-'); ?></p>

    <form method="post" style="margin-top:12px;">
      <input type="hidden" name="action" value="update_application_status">
      <label class="mini">Update Application Status</label>
      <select name="app_status">
        <?php
        $cur = $client['app_status'] ?? 'pending';
        foreach (['pending','under_review','approved','rejected'] as $st) {
            $sel = ($cur === $st) ? 'selected' : '';
            echo "<option value=\"$st\" $sel>$st</option>";
        }
        ?>
      </select>
      <button class="btn btn-primary" type="submit" style="margin-top:10px;">Save Status</button>
    </form>
  </div>
</div>

<div class="card">
  <h2>Couple Details</h2>
  <?php if (!$client['app_id']): ?>
    <p>No application data found for this user.</p>
  <?php else: ?>
    <div class="grid">
      <div>
        <h3 style="margin:0 0 8px;">Partner 1</h3>
        <p><b>Name:</b> <?php echo esc($client['partner1_name']); ?></p>
        <p><b>Age:</b> <?php echo esc($client['partner1_age']); ?></p>
        <p><b>Occupation:</b> <?php echo esc($client['partner1_occupation']); ?></p>
        <p><b>Blood:</b> <?php echo esc($client['partner1_blood_group']); ?></p>
        <p><b>Medical:</b> <?php echo esc($client['partner1_medical']); ?></p>
      </div>
      <div>
        <h3 style="margin:0 0 8px;">Partner 2</h3>
        <p><b>Name:</b> <?php echo esc($client['partner2_name']); ?></p>
        <p><b>Age:</b> <?php echo esc($client['partner2_age']); ?></p>
        <p><b>Occupation:</b> <?php echo esc($client['partner2_occupation']); ?></p>
        <p><b>Blood:</b> <?php echo esc($client['partner2_blood_group']); ?></p>
        <p><b>Medical:</b> <?php echo esc($client['partner2_medical']); ?></p>
      </div>
      <div>
        <h3 style="margin:0 0 8px;">Address</h3>
        <p><b>District:</b> <?php echo esc($client['district']); ?></p>
        <p><b>Address:</b> <?php echo esc($client['address']); ?></p>
      </div>
    </div>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Vote</h2>
  <?php if (!$vote): ?>
    <p>No vote submitted.</p>
  <?php else: ?>
    <p><b>Voted Child:</b> <?php echo esc($vote['child_code'] ?? '-'); ?> — <?php echo esc($vote['child_name'] ?? '-'); ?></p>
    <p><b>Vote Time:</b> <?php echo esc($vote['vote_date'] ?? '-'); ?></p>
    <p><b>Vote Status:</b> <?php echo esc($vote['vote_status'] ?? '-'); ?></p>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Documents</h2>
  <p class="mini">Total: <?php echo $docsTotal; ?> | Approved: <?php echo $docsApproved; ?> | Pending: <?php echo $docsPending; ?> | Rejected: <?php echo $docsRejected; ?></p>
  <table>
    <thead>
      <tr>
        <th>File</th>
        <th>Category</th>
        <th>Status</th>
        <th>Uploaded</th>
        <th>Review Notes</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$documents): ?>
        <tr><td colspan="5">No documents uploaded.</td></tr>
      <?php else: foreach ($documents as $d): ?>
        <tr>
          <td><?php echo esc($d['original_name']); ?></td>
          <td><?php echo esc($d['category']); ?></td>
          <td><?php echo esc($d['status']); ?></td>
          <td><?php echo esc($d['upload_date']); ?></td>
          <td><?php echo esc($d['review_notes'] ?? '-'); ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <h2>Appointments</h2>
  <table>
    <thead>
      <tr>
        <th>Type</th>
        <th>Title</th>
        <th>Date</th>
        <th>Time</th>
        <th>Location</th>
        <th>Status</th>
        <th>Confirmed</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$appointments): ?>
        <tr><td colspan="7">No appointments found.</td></tr>
      <?php else: foreach ($appointments as $a): ?>
        <tr>
          <td><?php echo esc($a['appointment_type']); ?></td>
          <td><?php echo esc($a['title'] ?? '-'); ?></td>
          <td><?php echo esc($a['appointment_date']); ?></td>
          <td><?php echo esc($a['appointment_time']); ?></td>
          <td><?php echo esc($a['meeting_location']); ?></td>
          <td><?php echo esc($a['status']); ?></td>
          <td><?php echo ((int)$a['confirmed'] === 1) ? 'Yes' : 'No'; ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <h2>Audit Logs (Latest 10)</h2>
  <table>
    <thead>
      <tr>
        <th>Action</th>
        <th>New Value</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$auditLogs): ?>
        <tr><td colspan="3">No audit logs yet.</td></tr>
      <?php else: foreach ($auditLogs as $l): ?>
        <tr>
          <td><?php echo esc($l['action']); ?></td>
          <td><?php echo esc($l['new_value']); ?></td>
          <td><?php echo esc($l['created_at']); ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

  <form method="post" style="margin-top:12px;">
    <input type="hidden" name="action" value="add_note">
    <label class="mini">Add Chief Note (saved into audit_logs)</label>
    <textarea name="note" rows="3" placeholder="Write a note..."></textarea>
    <button class="btn btn-outline" type="submit" style="margin-top:10px;">Save Note</button>
  </form>
</div>

</body>
</html>
