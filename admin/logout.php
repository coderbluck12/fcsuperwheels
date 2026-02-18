<?php
session_start();
include_once('inc/access_log.php');

// Log logout before destroying session
if (isset($_SESSION['current_user'])) {
    log_access('LOGOUT', 'logout.php', null, $_SESSION['current_user']);
}

@session_destroy();
unset($_SESSION['current_user']);
header("location:index.php");
?>