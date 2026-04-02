<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
  $_SESSION['message'] = 'Invalid stock ID.';
  header('Location: stock.php');
  exit;
}

$sql = "SELECT s.id, s.product_id, s.quantity, p.name as product_name 
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

$stock = $result->fetch_assoc();

$error = '';

function getProducts($conn) {
  $sql = "SELECT id, name FROM products ORDER BY name";
  $result = $conn->query($sql);
  return $result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $product_id = intval($_POST['product_id'] ?? 0);
  $quantity = intval($_POST['quantity'] ?? 0);

  if ($product_id <= 0) {
    $error = 'Please select a product.';
  } elseif ($quantity < 0) {
    $error = 'Quantity cannot be negative.';
  } else {
    $check_sql = "SELECT id FROM stock WHERE product_id = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $product_id, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
      $error = 'Stock record for this product already exists.';
    } else {
      $sql = "UPDATE stock SET product_id = ?, quantity = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("iii", $product_id, $quantity, $id);

      if ($stmt->execute()) {
        $_SESSION['message'] = 'Stock record updated successfully!';
        header('Location: stock.php');
        exit;
      } else {
        $error = 'Something went wrong. Please try again.';
      }
    }
  }
}

$products = getProducts($conn);

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
      <h3>Edit Stock Record</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="editstock.php?id=<?= $id ?>" method="POST" id="edit-form" autocomplete="off">
        <div class="form-group">
          <label for="product_id">Product *</label>
          <select name="product_id" id="product_id" required>
            <option value="">Select Product</option>
            <?php foreach ($products as $prod): ?>
              <option value="<?= $prod['id'] ?>" <?= $stock['product_id'] == $prod['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($prod['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="quantity">Quantity *</label>
          <input type="number" name="quantity" id="quantity" value="<?= $stock['quantity'] ?>" min="0" required>
        </div>

        <div class="form-buttons">
          <button type="submit" class="submit-btn">Update Stock</button>
          <a href="stock.php" class="cancel-btn">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.form-group {
  margin-bottom: 20px;
}
.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #333;
}
.form-group input,
.form-group select {
  width: 100%;
  max-width: 400px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
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
</style>

<?php require('../components/footer.php'); ?>