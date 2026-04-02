<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
  $_SESSION['message'] = 'Invalid stock ID.';
  header('Location: stock.php');
  exit;
}

$sql = "SELECT s.id, p.name as product_name 
        FROM stock s 
        JOIN products p ON s.product_id = p.id 
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['message'] = 'Stock record not found.';
  header('Location: stock.php');
  exit;
}

$delete_sql = "DELETE FROM stock WHERE id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $id);

if ($delete_stmt->execute()) {
  $_SESSION['message'] = 'Stock record deleted successfully!';
} else {
  $_SESSION['message'] = 'Something went wrong.';
}

header('Location: stock.php');
exit;
?>