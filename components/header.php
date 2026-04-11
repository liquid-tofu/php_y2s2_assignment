<?php
// prevent duplicate start
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Cross-Site Request Forgery??
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once(__DIR__ . '/../db.php');

// remember me baby
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
  $_SESSION['user_id'] = $_COOKIE['user_id'];

  $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->bind_param("i", $_SESSION['user_id']);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if ($user) {
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
  } else {
    setcookie('user_id', '', time() - 3600, "/");
    header("Location: ../login.php");
    exit;
  }
}

if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit;
}

// for batch config
$per_page   = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$limit      = $per_page;
$batch      = isset($_GET['batch'])    ? (int)$_GET['batch']    : 1;

if (isset($origin)) {
  require(__DIR__ . '/../components/page_logic/count_compat.php');
}
if (ob_get_level() > 0) {
  ob_end_clean();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stock Management</title>
  <link rel="icon" type="image/x-icon" href="/resources/Logo.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="/styles/com.css">
  <link rel="stylesheet" href="/styles/content.css">
  <link rel="stylesheet" href="/styles/edit.css">
  <link rel="stylesheet" href="/styles/add.css">
</head>
<body>


