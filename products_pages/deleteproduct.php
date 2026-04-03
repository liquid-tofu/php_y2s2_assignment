<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['message'] = 'Invalid product ID.';
    header('Location: products.php');
    exit;
}

$sql = "SELECT name FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = 'Product not found.';
    header('Location: products.php');
    exit;
}

$delete_sql = "DELETE FROM products WHERE id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $id);

if ($delete_stmt->execute()) {
    $_SESSION['message'] = 'Product deleted successfully!';
} else {
    if ($delete_stmt->getErrorCode() == 1451) {
        $_SESSION['message'] = 'Cannot delete this product because it has related stock movement records.';
    } else {
        $_SESSION['message'] = 'Error: ' . $delete_stmt->getErrorMessage();
    }
}

header('Location: products.php');
exit;
?>