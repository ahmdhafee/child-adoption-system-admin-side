<?php
declare(strict_types=1);

session_start();


unset(
    $_SESSION['officer_id'],
    $_SESSION['officer_role'],
    $_SESSION['officer_email'],
    $_SESSION['officer_name'],
    $_SESSION['login_time']
);

session_destroy();


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");


header("Location: officer_login.php?logout=success");
exit;
