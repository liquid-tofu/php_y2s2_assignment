<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['message'] = 'Invalid movement ID.';
    $_SESSION['message_type'] = 'error';
    header('Location: stock_movement.php');
    exit;
}

// Check if stock movement exists
$sql = "SELECT id FROM stock_movement WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = 'Stock movement record not found.';
    $_SESSION['message_type'] = 'error';
    header('Location: stock_movement.php');
    exit;
}

// Delete the stock movement record
$delete_sql = "DELETE FROM stock_movement WHERE id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $id);

try {
    if ($delete_stmt->execute()) {
        $_SESSION['message'] = 'Stock movement deleted successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Something went wrong.';
        $_SESSION['message_type'] = 'error';
    }
} catch (Exception $e) {
    $_SESSION['message'] = 'Error: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header('Location: stock_movement.php');
exit;
?>