<?php
require('../db.php');
session_start();

function getProducts($conn) {
  $sql = "SELECT id, name, price FROM products ORDER BY name";
  $result = $conn->query($sql);
  if (!$result) {
    return [];
  }
  $products = [];
  while ($row = $result->fetch_assoc()) {
    $products[] = $row;
  }
  return $products;
}

$all_products = getProducts($conn);
$product_map = [];
foreach ($all_products as $p) {
  $product_map[(int)$p['id']] = $p;
}

$so_id = (int)($_GET['id'] ?? 0);
if ($so_id <= 0) {
  header('Location: so.php');
  exit;
}

$sql = "SELECT * FROM so WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $so_id);
$stmt->execute();
$result = $stmt->get_result();
$so = $result ? $result->fetch_assoc() : null;

if (!$so) {
  header('Location: so.php');
  exit;
}

$sql = "SELECT * FROM soi WHERE so_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $so_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$cus_name = $so['cus_name'];
$cus_email = $so['cus_email'];
$order_date = $so['order_date'];
$status = $so['status'];
$total_amount = $so['total_amount'];

require('../components/header.php');
?>
<link rel="stylesheet" href="/styles/content.css">
<link rel="stylesheet" href="/styles/add.css">
<?php require('../components/sidebar.php'); ?>

<div class="main">
  <div class="topbar">
    <h3>Stock Management System</h3>
    <div class="user">
      <i class="bi bi-person-circle"></i>
      <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
    </div>
  </div>

  <div class="content">
    <div id="content-container">
      <h3>View Sales Order</h3>
      <hr>

      <div class="add-form po-form">
        <section class="po-section">
          <h4>Sale Info</h4>
          <div class="po-grid">
            <div>
              <label>Customer Name</label>
              <input type="text" value="<?= htmlspecialchars($cus_name) ?>" disabled>
            </div>
            <div>
              <label>Customer Email</label>
              <input type="email" value="<?= htmlspecialchars($cus_email) ?>" disabled>
            </div>
            <div>
              <label>Order Date</label>
              <input type="date" value="<?= htmlspecialchars($order_date) ?>" disabled>
            </div>
            <div>
              <label>Status</label>
              <input type="text" value="<?= htmlspecialchars($status) ?>" disabled>
            </div>
          </div>
        </section>

        <section class="po-section">
          <h4>Item List</h4>
          <div id="items-container">
            <?php foreach ($items as $item): ?>
              <?php
                $pid = (int)$item['product_id'];
                $qty = (int)$item['quantity'];
                $upr = (float)$item['unit_price'];
                $row_total = $qty * $upr;
                $product_name = isset($product_map[$pid]) ? $product_map[$pid]['name'] : 'Unknown';
              ?>
              <div class="item-row">
                <div class="item-col item-product">
                  <label>Product</label>
                  <input type="text" value="<?= htmlspecialchars($product_name) ?>" disabled>
                </div>
                <div class="item-col">
                  <label>Qty</label>
                  <input type="number" value="<?= $qty ?>" disabled>
                </div>
                <div class="item-col">
                  <label>Unit Price</label>
                  <input type="number" value="<?= number_format($upr, 2, '.', '') ?>" disabled>
                </div>
                <div class="item-col">
                  <label>Row Total</label>
                  <input type="text" value="$<?= number_format($row_total, 2) ?>" disabled>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="po-summary">
          <label>Total Value</label>
          <input type="text" value="$<?= number_format($total_amount, 2) ?>" disabled>
        </section>

        <section id="view-btn">
          <a href="so.php" id="cancel-btn">Cancel</a>
        </section>
      </div>
    </div>
  </div>
</div>

<?php require('../components/footer.php'); ?>