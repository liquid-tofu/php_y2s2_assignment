<?php
require('../../db.php');
session_start();

function require_role($page, ...$roles) {
  if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
  }
  if (!in_array(strtolower($_SESSION['role']), array_map('strtolower', $roles))) {
    $_SESSION['message'] = 'You do not have permission to do this.';
    header("Location: $page");
    exit;
  }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $_SESSION['message'] = 'Invalid request.';
  header('Location: index.php');
  exit;
}
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
  $_SESSION['message'] = 'Security validation failed.';
  header('Location: dashboard.php');
  exit;
}
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
  $_SESSION['message'] = 'Invalid security token.';
  header('Location: dashboard.php');
  exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$tbl = isset($_POST['tbl']) ? $_POST['tbl'] : '';
$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : 'index.php';
$page = isset($_POST['page']) ? $_POST['page'] : 'index.php';

require_role($page, 'admin', 'manager');

$allowed_tables = ['users', 'products', 'po', 'so', 'categories', 'stock', 'stock_movement', 'suppliers'];
if (!in_array($tbl, $allowed_tables)) {
  $_SESSION['message'] = 'Invalid table.';
  header("Location: $return_url");
  exit;
}
if ($tbl === 'users' && strtolower($_SESSION['role']) !== 'admin') {
  $_SESSION['message'] = 'Only administrators can delete users.';
  header("Location: $return_url");
  exit;
}

$sql = "DELETE FROM {$tbl} WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  $_SESSION['message'] = 'Record deleted successfully!';
} else {
  $_SESSION['message'] = 'Something went wrong.';
}

header("Location: $return_url");
exit;
?>
