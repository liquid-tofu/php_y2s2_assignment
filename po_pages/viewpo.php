<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
  $_SESSION['message'] = 'Invalid purchase order ID.';
  header('Location: po.php');
  exit;
}

$sql = "SELECT po.*, s.name as supplier_name, s.email, s.phone 
        FROM po 
        JOIN suppliers s ON po.supplier_id = s.id 
        WHERE po.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['message'] = 'Purchase order not found.';
  header('Location: po.php');
  exit;
}

$order = $result->fetch_assoc();

$items_sql = "SELECT poi.*, p.name as product_name 
              FROM poi 
              JOIN products p ON poi.product_id = p.id 
              WHERE poi.po_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $id);
$items_stmt->execute();
$items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function getStatusBadge($status) {
  $badges = [
    'PENDING' => '<span class="status-pending">PENDING</span>',
    'APPROVED' => '<span class="status-approved">APPROVED</span>',
    'REJECTED' => '<span class="status-rejected">REJECTED</span>',
    'CANCELLED' => '<span class="status-cancelled">CANCELLED</span>'
  ];
  return $badges[$status] ?? $status;
}

require('../components/header.php');
?>
<link rel="stylesheet" href="/styles/content.css">
<?php require('../components/sidebar.php'); ?>

<div class="main">
  <div class="topbar">
    <h3>Stock Management System</h3>
    <div class="user">
      <i class="bi bi-person-circle"></i> Administrator
    </div>
  </div>

  <div class="content">
    <div id="content-container">
      <h3>Purchase Order Details</h3>
      <hr>

      <div class="order-info">
        <div class="info-row">
          <strong>PO ID:</strong> <?= $order['id'] ?>
        </div>
        <div class="info-row">
          <strong>Supplier:</strong> <?= htmlspecialchars($order['supplier_name']) ?>
        </div>
        <div class="info-row">
          <strong>Contact:</strong> <?= htmlspecialchars($order['email']) ?> | <?= htmlspecialchars($order['phone']) ?>
        </div>
        <div class="info-row">
          <strong>Order Date:</strong> <?= htmlspecialchars($order['order_date']) ?>
        </div>
        <div class="info-row">
          <strong>Status:</strong> <?= getStatusBadge($order['status']) ?>
        </div>
        <div class="info-row">
          <strong>Created At:</strong> <?= htmlspecialchars($order['created_at']) ?>
        </div>
      </div>

      <h4>Order Items</h4>
      <table id="content-table">
        <thead>
          <tr>
            <th>#