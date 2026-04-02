<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
  $_SESSION['message'] = 'Invalid sales order ID.';
  header('Location: so.php');
  exit;
}

$sql = "SELECT * FROM so WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['message'] = 'Sales order not found.';
  header('Location: so.php');
  exit;
}

$order = $result->fetch_assoc();

$items_sql = "SELECT soi.*, p.name as product_name 
              FROM soi 
              JOIN products p ON soi.product_id = p.id 
              WHERE soi.so_id = ?";
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
      <h3>Sales Order Details</h3>
      <hr>

      <div class="order-info">
        <div class="info-row">
          <strong>SO ID:</strong> <?= $order['id'] ?>
        </div>
        <div class="info-row">
          <strong>Customer Name:</strong> <?= htmlspecialchars($order['cus_name']) ?>
        </div>
        <div class="info-row">
          <strong>Customer Email:</strong> <?= htmlspecialchars($order['cus_email']) ?>
        </div>
        <div class="info-row">
          <strong>Order Date:</strong> <?= htmlspecialchars($order['order_date']) ?>
        </div>
        <div class="info-row">
          <strong>Status:</strong> <?= getStatusBadge($order['status']) ?>
        </div>
        <div class="info-row">
          <strong>Total Amount:</strong> <span class="total-amount">$<?= number_format($order['total_amount'], 2) ?></span>
        </div>
      </div>

      <h4>Order Items</h4>
      <table id="content-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total Price</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;

  if (count($items) > 0):
    foreach ($items as $item):
  ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($item['product_name']) ?></td>
        <td><?= $item['quantity'] ?></td>
        <td>$<?= number_format($item['unit_price'], 2) ?></td>
        <td>$<?= number_format($item['total_price'], 2) ?></td>
      </tr>
  <?php
    endforeach;
  else:
  ?>
      <tr>
        <td colspan="5" style="text-align:center;">No items found.</td>
      </tr>
  <?php endif; ?>
</tbody>