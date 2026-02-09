<?php
declare(strict_types=1);

session_start();


if (!isset($_SESSION['officer_id'], $_SESSION['officer_role'])) {
    header("Location: .\officer_login.php?error=unauthorized");
    exit;
}


$timeout = 30 * 60;

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout) {
    session_destroy();
    header("Location:  .\officer_auth.php?error=session_expired");
    exit;
}


$_SESSION['login_time'] = time();
