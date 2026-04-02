<?php
session_start();

$_SESSION = [];
session_destroy();

setcookie('user_id', '', time() - 3600, "/");

session_start();
$_SESSION['message'] = "You have been logged out.";

header("Location: login.php");
exit;
?>