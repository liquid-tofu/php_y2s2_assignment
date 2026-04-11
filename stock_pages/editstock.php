<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$return_url = $_GET['return_url'] ?? ($_POST['return_url'] ?? 'stock.php');
if ($id <= 0) {
  $_SESSION['message'] = 'Invalid stock ID.';
  header("Location: $return_url");
  exit;
}

$sql = "SELECT s.id, s.product_id, s.quantity, p.name AS product_name
        FROM stock s
        JOIN products p ON s.product_id = p.id
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$stock = $result ? $result->fetch_assoc() : null;

if (!$stock) {
  $_SESSION['message'] = 'Stock record not found.';
  header("Location: $return_url");
  exit;
}

$error = '';

$products = [];
$product_result = $conn->query("SELECT id, name FROM products ORDER BY name");
if ($product_result) {
  $products = $product_result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

  if ($product_id <= 0) {
    $error = 'Please select a product.';
  } elseif ($quantity < 0) {
    $error = 'Quantity cannot be negative.';
  } else {
    $check = $conn->prepare("SELECT id FROM stock WHERE product_id = ? AND id != ?");
    $check->bind_param("ii", $product_id, $id);
    $check->execute();
    $check_result = $check->get_result();
    if ($check_result && $check_result->num_rows > 0) {
      $error = 'Stock record for this product already exists.';
    } else {
      $up = $conn->prepare("UPDATE stock SET product_id = ?, quantity = ? WHERE id = ?");
      $up->bind_param("iii", $product_id, $quantity, $id);
      if ($up->execute()) {
        $_SESSION['message'] = 'Stock record updated successfully!';
        header("Location: $return_url");
        exit;
      }
      $error = 'Something went wrong. Please try again.';
    }
  }

  if ($error !== '') {
    $stock['product_id'] = $product_id;
    $stock['quantity'] = $quantity;
  }
}

require('../components/header.php');
?>
<link rel="stylesheet" href="/styles/content.css">
<?php require('../components/sidebar.php'); ?>

<div class="main">
  <div class="topbar">
    <h3>Stock Management System</h3>
    <div class="user">
      <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
    </div>
  </div>
  <div class="content">
    <div id="content-container">
      <h3>Edit Stock Record</h3>
      <hr>
      <?php if ($error !== ''): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form action="editstock.php?id=<?= $id ?>" method="POST" class="edit-form" autocomplete="off">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url) ?>">
        <div class="form-group">
          <label for="product_id">Product *</label>
          <select name="product_id" id="product_id" required>
            <option value="">Select Product</option>
            <?php foreach ($products as $prod): ?>
              <option value="<?= (int)$prod['id'] ?>" <?= ((int)$stock['product_id'] === (int)$prod['id']) ? 'selected' : '' ?>><?= htmlspecialchars($prod['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="quantity">Quantity *</label>
          <input type="number" name="quantity" id="quantity" value="<?= htmlspecialchars((string)$stock['quantity']) ?>" min="0" required>
        </div>
        <div class="form-buttons">
          <a href="<?= htmlspecialchars($return_url) ?>" id="cancel-btn">Cancel</a>
          <button type="submit" id="update-btn">Update Stock</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require('../components/footer.php'); ?>
