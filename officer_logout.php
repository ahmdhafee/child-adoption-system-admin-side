<?php
declare(strict_types=1);

session_start();

// Unset all officer session variables
unset(
    $_SESSION['officer_id'],
    $_SESSION['officer_role'],
    $_SESSION['officer_email'],
    $_SESSION['officer_name'],
    $_SESSION['login_time']
);

// Destroy session completely
session_destroy();

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Redirect to officer login
header("Location: officer_login.php?logout=success");
exit;
