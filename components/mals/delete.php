<?php
require('../db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  if ($stmt->affected_rows > 0) {
    $_SESSION['message'] = 'Product deleted successfully!';
  } else {
    $_SESSION['message'] = 'Product not found.';
  }
} else {
  $_SESSION['message'] = 'Something went wrong.';
}

header('Location: products.php');
exit;
?>