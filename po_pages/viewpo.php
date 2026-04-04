<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
  $_SESSION['message'] = 'Invalid purchase order ID.';
  $_SESSION['message_type'] = 'error';
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
  $_SESSION['message_type'] = 'error';
  header('Location: po.php');
  exit;
}

$order = $result->fetch_assoc();

$items_sql = "SELECT poi.*, p.name as product_name, p.price
              FROM poi 
              JOIN products p ON poi.product_id = p.id 
              WHERE poi.po_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = [];
$total_amount = 0;
while ($row = $items_result->fetch_assoc()) {
  $items[] = $row;
  $total_amount += $row['quantity'] * $row['unit_price'];
}

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

      <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?= $_SESSION['message_type'] ?? 'success' ?>">
          <?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        </div>
      <?php endif; ?>

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
            <th>#</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($items) > 0): ?>
            <?php $counter = 1; ?>
            <?php foreach ($items as $item): ?>
              <tr>
                <td><?= $counter++ ?></td>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= number_format($item['quantity']) ?></td>
                <td>$<?= number_format($item['unit_price'], 2) ?></td>
                <td>$<?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align: center; color: #b2b2b2 !important;">No items found</td>
            </tr>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" style="text-align: right;"><strong>Grand Total:</strong></td>
            <td><strong>$<?= number_format($total_amount, 2) ?></strong></td>
          </tr>
        </tfoot>
      </table>

      <div class="form-buttons">
        <a href="po.php" class="cancel-btn">Back to Purchase Orders</a>
      </div>
    </div>
  </div>
</div>

<style>
.order-info {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 30px;
}
.info-row {
  margin-bottom: 10px;
  padding: 5px 0;
  border-bottom: 1px solid #eee;
}
.info-row strong {
  display: inline-block;
  width: 120px;
  color: #555;
}
#content-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
#content-table th,
#content-table td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: left;
}
#content-table th {
  background-color: #f2f2f2;
  font-weight: bold;
}
#content-table tfoot td {
  background-color: #f8f9fa;
  font-weight: bold;
}
.status-pending {
  background: #ffc107;
  color: #000;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}
.status-approved {
  background: #28a745;
  color: #fff;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}
.status-rejected {
  background: #dc3545;
  color: #fff;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}
.status-cancelled {
  background: #6c757d;
  color: #fff;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}
.form-buttons {
  margin-top: 30px;
  display: flex;
  gap: 15px;
}
.submit-btn {
  background: #00BFCB;
  color: white;
  padding: 10px 24px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  text-decoration: none;
  display: inline-block;
}
.submit-btn:hover {
  background: #00a5b0;
}
.cancel-btn {
  background: #6c757d;
  color: white;
  padding: 10px 24px;
  text-decoration: none;
  border-radius: 6px;
  font-size: 14px;
  display: inline-block;
}
.cancel-btn:hover {
  background: #5a6268;
}
.message {
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
}
.error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}
.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}
h4 {
  margin-top: 20px;
  margin-bottom: 10px;
  color: #333;
}
#content-container h3,
#content-container h4 {
  color: #b2b2b2 !important;
}
#content-container {
  color: #000;
}

* tr {
  background-color: #fff;
}
</style>

<?php require('../components/footer.php'); ?>