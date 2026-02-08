<?php
declare(strict_types=1);

require_once '../officer_auth.php';
require_once '../officer_db.php';

header('Content-Type: application/json');

$role = $_SESSION['officer_role'] ?? '';
if (!in_array($role, ['chief','admin'], true)) {
  http_response_code(403);
  echo json_encode(['success'=>false,'message'=>'Access denied']);
  exit;
}

$isChief = ($role === 'chief');
$isAdmin = ($role === 'admin');

function ok(array $d=[]){ echo json_encode(array_merge(['success'=>true],$d)); exit; }
function fail(string $m,int $c=400){ http_response_code($c); echo json_encode(['success'=>false,'message'=>$m]); exit; }

function sanitize_filename(string $name): string {
  $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
  return $name ?: 'file';
}

function calcAge(?string $dob): int {
  if (!$dob) return 0;
  try {
    $d = new DateTime($dob);
    $now = new DateTime();
    return (int)$now->diff($d)->y;
  } catch(Exception $e) {
    return 0;
  }
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {

  /* =========================
     LIST (admin & chief)
     ========================= */
  if ($action === 'list') {

    // Join institute name if exists
    $sql = "
      SELECT
        c.id,
        c.child_code,
        c.photo,
        c.full_name,
        c.gender,
        c.date_of_birth,
        c.added_at,
        c.status,
        c.blood_group,
        c.hair_color,
        c.eyes_color,
        c.skin_color,
        c.height_cm,
        c.weight_kg,
        c.religion,
        c.medical_condition,
        c.district,
        c.institute_id,
        i.name AS institute_name
      FROM children c
      LEFT JOIN institutes i ON i.id = c.institute_id
      ORDER BY c.id DESC
    ";

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Build response with role-based filtering
    $children = [];
    $stats = [
      'total_children' => 0,
      'available_children' => 0,
      'pending_children' => 0,
      'adopted_children' => 0
    ];

    foreach ($rows as $r) {
      $age = calcAge($r['date_of_birth'] ?? null);

      // Stats
      $stats['total_children']++;
      if (($r['status'] ?? '') === 'available') $stats['available_children']++;
      if (($r['status'] ?? '') === 'pending' || ($r['status'] ?? '') === 'reserved') $stats['pending_children']++;
      if (($r['status'] ?? '') === 'adopted') $stats['adopted_children']++;

      if ($isAdmin) {
        // ADMIN: NO photo, NO district, NO full name, NO institute_name
        $children[] = [
          'id' => $r['id'],
          'child_code' => $r['child_code'],
          'name' => 'CONFIDENTIAL', // masked
          'age' => $age,
          'gender' => $r['gender'],
          'date_of_birth' => $r['date_of_birth'],
          'added_at' => $r['added_at'],
          'status' => $r['status'],
          'blood_group' => $r['blood_group'],
          'hair_color' => $r['hair_color'],
          'eyes_color' => $r['eyes_color'],
          'skin_color' => $r['skin_color'],
          'height_cm' => $r['height_cm'],
          'weight_kg' => $r['weight_kg'],
          'religion' => $r['religion'],
          'medical_condition' => $r['medical_condition'],
        ];
      } else {
        // CHIEF: full view
        $children[] = [
          'id' => $r['id'],
          'child_code' => $r['child_code'],
          'photo' => $r['photo'],
          'name' => $r['full_name'],
          'full_name' => $r['full_name'],
          'age' => $age,
          'gender' => $r['gender'],
          'date_of_birth' => $r['date_of_birth'],
          'added_at' => $r['added_at'],
          'status' => $r['status'],
          'blood_group' => $r['blood_group'],
          'hair_color' => $r['hair_color'],
          'eyes_color' => $r['eyes_color'],
          'skin_color' => $r['skin_color'],
          'height_cm' => $r['height_cm'],
          'weight_kg' => $r['weight_kg'],
          'religion' => $r['religion'],
          'medical_condition' => $r['medical_condition'],
          'district' => $r['district'],
          'institute_id' => $r['institute_id'],
          'institute_name' => $r['institute_name'],
        ];
      }
    }

    ok(['children'=>$children,'stats'=>$stats, 'role'=>$role]);
  }

  /* =========================
     GET single (admin & chief)
     ========================= */
  if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) fail('Invalid id');

    $stmt = $pdo->prepare("
      SELECT c.*, i.name AS institute_name
      FROM children c
      LEFT JOIN institutes i ON i.id = c.institute_id
      WHERE c.id = ?
      LIMIT 1
    ");
    $stmt->execute([$id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$r) fail('Child not found',404);

    $age = calcAge($r['date_of_birth'] ?? null);

    if ($isAdmin) {
      ok(['child'=>[
        'id'=>$r['id'],
        'child_code'=>$r['child_code'],
        'full_name'=>'CONFIDENTIAL',
        'age'=>$age,
        'gender'=>$r['gender'] ?? '',
        'blood_group'=>$r['blood_group'] ?? '',
        'hair_color'=>$r['hair_color'] ?? '',
        'eyes_color'=>$r['eyes_color'] ?? '',
        'skin_color'=>$r['skin_color'] ?? '',
        'height_cm'=>$r['height_cm'] ?? '',
        'weight_kg'=>$r['weight_kg'] ?? '',
        'religion'=>$r['religion'] ?? '',
        'medical_condition'=>$r['medical_condition'] ?? '',
        'status'=>$r['status'] ?? '',
        'date_of_birth'=>$r['date_of_birth'] ?? '',
        'added_at'=>$r['added_at'] ?? ''
      ]]);
    }

    ok(['child'=>[
      'id'=>$r['id'],
      'child_code'=>$r['child_code'],
      'photo'=>$r['photo'],
      'full_name'=>$r['full_name'],
      'age'=>$age,
      'gender'=>$r['gender'] ?? '',
      'blood_group'=>$r['blood_group'] ?? '',
      'hair_color'=>$r['hair_color'] ?? '',
      'eyes_color'=>$r['eyes_color'] ?? '',
      'skin_color'=>$r['skin_color'] ?? '',
      'height_cm'=>$r['height_cm'] ?? '',
      'weight_kg'=>$r['weight_kg'] ?? '',
      'religion'=>$r['religion'] ?? '',
      'medical_condition'=>$r['medical_condition'] ?? '',
      'district'=>$r['district'] ?? '',
      'institute_id'=>$r['institute_id'] ?? '',
      'institute_name'=>$r['institute_name'] ?? '',
      'status'=>$r['status'] ?? '',
      'date_of_birth'=>$r['date_of_birth'] ?? '',
      'added_at'=>$r['added_at'] ?? ''
    ]]);
  }

  /* =========================
     CREATE / UPDATE / DELETE
     CHIEF ONLY
     ========================= */
  if (in_array($action, ['create','update','delete'], true)) {
    if (!$isChief) fail('Only Chief can perform this action',403);
  }

  // CREATE
  if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $status = trim($_POST['status'] ?? 'available');
    $dateRegistered = trim($_POST['dateRegistered'] ?? date('Y-m-d'));

    $blood_group = trim($_POST['blood_group'] ?? '');
    $hair_color = trim($_POST['hair_color'] ?? '');
    $eyes_color = trim($_POST['eyes_color'] ?? '');
    $skin_color = trim($_POST['skin_color'] ?? '');
    $height_cm = $_POST['height_cm'] ?? null;
    $weight_kg = $_POST['weight_kg'] ?? null;
    $religion = trim($_POST['religion'] ?? '');
    $medical_condition = trim($_POST['medical_condition'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $institute_id = (int)($_POST['instituteId'] ?? 0);

    if ($full_name === '' || $dob === '' || $gender === '' || $status === '' || $dateRegistered === '') {
      fail('Please fill required fields');
    }

    // child_code auto
    $child_code = "FB-CH-" . strtoupper(bin2hex(random_bytes(3)));

    // Photo upload
    $photoName = null;
    if (!empty($_FILES['photo']['name'])) {
      $uploadDir = "../uploads/children/";
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

      $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
      $allowed = ['jpg','jpeg','png','webp'];
      if (!in_array($ext, $allowed, true)) fail('Photo must be JPG/PNG/WEBP');

      $safe = sanitize_filename(pathinfo($_FILES['photo']['name'], PATHINFO_FILENAME));
      $photoName = $safe . "_" . time() . "." . $ext;
      $dest = $uploadDir . $photoName;

      if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
        fail('Failed to upload photo');
      }
    }

    $stmt = $pdo->prepare("
      INSERT INTO children
      (child_code, photo, full_name, gender, date_of_birth, added_at, status,
       blood_group, hair_color, eyes_color, skin_color, height_cm, weight_kg,
       religion, medical_condition, district, institute_id)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([
      $child_code, $photoName, $full_name, $gender, $dob, $dateRegistered, $status,
      $blood_group, $hair_color, $eyes_color, $skin_color, $height_cm, $weight_kg,
      $religion, $medical_condition, $district, ($institute_id>0?$institute_id:null)
    ]);

    ok(['message'=>'Child added successfully']);
  }

  // UPDATE
  if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) fail('Invalid id');

    $full_name = trim($_POST['full_name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $status = trim($_POST['status'] ?? 'available');
    $dateRegistered = trim($_POST['dateRegistered'] ?? date('Y-m-d'));

    $blood_group = trim($_POST['blood_group'] ?? '');
    $hair_color = trim($_POST['hair_color'] ?? '');
    $eyes_color = trim($_POST['eyes_color'] ?? '');
    $skin_color = trim($_POST['skin_color'] ?? '');
    $height_cm = $_POST['height_cm'] ?? null;
    $weight_kg = $_POST['weight_kg'] ?? null;
    $religion = trim($_POST['religion'] ?? '');
    $medical_condition = trim($_POST['medical_condition'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $institute_id = (int)($_POST['instituteId'] ?? 0);

    if ($full_name === '' || $dob === '' || $gender === '' || $status === '' || $dateRegistered === '') {
      fail('Please fill required fields');
    }

    // existing photo
    $stmt = $pdo->prepare("SELECT photo FROM children WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$old) fail('Child not found',404);

    $photoName = $old['photo'];

    if (!empty($_FILES['photo']['name'])) {
      $uploadDir = "../uploads/children/";
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

      $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
      $allowed = ['jpg','jpeg','png','webp'];
      if (!in_array($ext, $allowed, true)) fail('Photo must be JPG/PNG/WEBP');

      $safe = sanitize_filename(pathinfo($_FILES['photo']['name'], PATHINFO_FILENAME));
      $newName = $safe . "_" . time() . "." . $ext;
      $dest = $uploadDir . $newName;

      if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
        fail('Failed to upload photo');
      }

      // delete old photo file
      if ($photoName && file_exists($uploadDir.$photoName)) @unlink($uploadDir.$photoName);
      $photoName = $newName;
    }

    $stmt = $pdo->prepare("
      UPDATE children SET
        photo=?,
        full_name=?,
        gender=?,
        date_of_birth=?,
        added_at=?,
        status=?,
        blood_group=?,
        hair_color=?,
        eyes_color=?,
        skin_color=?,
        height_cm=?,
        weight_kg=?,
        religion=?,
        medical_condition=?,
        district=?,
        institute_id=?
      WHERE id=?
    ");
    $stmt->execute([
      $photoName, $full_name, $gender, $dob, $dateRegistered, $status,
      $blood_group, $hair_color, $eyes_color, $skin_color, $height_cm, $weight_kg,
      $religion, $medical_condition, $district, ($institute_id>0?$institute_id:null),
      $id
    ]);

    ok(['message'=>'Child updated successfully']);
  }

  // DELETE
  if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) fail('Invalid id');

    $stmt = $pdo->prepare("SELECT photo FROM children WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$old) fail('Child not found',404);

    $pdo->prepare("DELETE FROM children WHERE id=?")->execute([$id]);

    $uploadDir = "../uploads/children/";
    if (!empty($old['photo']) && file_exists($uploadDir.$old['photo'])) {
      @unlink($uploadDir.$old['photo']);
    }

    ok(['message'=>'Child deleted successfully']);
  }

  fail('Unknown action');

} catch (PDOException $e) {
  error_log("children_api.php error: ".$e->getMessage());
  fail('Server error',500);
}
