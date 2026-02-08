<?php
declare(strict_types=1);

session_start();

// Officer must be logged in
if (!isset($_SESSION['officer_id'], $_SESSION['officer_role'])) {
    header("Location: .\officer_login.php?error=unauthorized");
    exit;
}

// Optional: session timeout (30 minutes)
$timeout = 30 * 60;

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout) {
    session_destroy();
    header("Location:  .\officer_auth.php?error=session_expired");
    exit;
}

// Refresh activity time
$_SESSION['login_time'] = time();
