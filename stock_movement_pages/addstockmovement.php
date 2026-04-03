<?php
require('../db.php');
session_start();

$error = '';

function getProducts($conn) {
  $sql = "SELECT id, name FROM products ORDER BY name";
  $result = $conn->query($sql);
  return $result->fetch_all(MYSQLI_ASSOC);
}

function updateStockQuantity($conn, $product_id, $type, $quantity) {
  if ($type == 'IN') {
    $sql = "UPDATE stock SET quantity = quantity + ? WHERE product_id = ?";
  } elseif ($type == 'OUT') {
    $sql = "UPDATE stock SET quantity = quantity - ? WHERE product_id = ?";
  } else {
    $sql = "UPDATE stock SET quantity = ? WHERE product_id = ?";
  }
  
  $stmt = $conn->prepare($sql);
  if ($type == 'ADJUST') {
    $stmt->bind_param("ii", $quantity, $product_id);
  } else {
    $stmt->bind_param("ii", $quantity, $product_id);
  }
  return $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $product_id = intval($_POST['product_id'] ?? 0);
  $type = $_POST['type'] ?? '';
  $quantity = intval($_POST['quantity'] ?? 0);
  $note = trim($_POST['note'] ?? '');

  if ($product_id <= 0) {
    $error = 'Please select a product.';
  } elseif (!in_array($type, ['IN', 'OUT', 'ADJUST'])) {
    $error = 'Please select a valid type.';
  } elseif ($quantity <= 0) {
    $error = 'Quantity must be greater than 0.';
  } else {
    $check_stock_sql = "SELECT quantity FROM stock WHERE product_id = ?";
    $check_stock_stmt = $conn->prepare($check_stock_sql);
    $check_stock_stmt->bind_param("i", $product_id);
    $check_stock_stmt->execute();
    $stock_result = $check_stock_stmt->get_result();
    
    if ($stock_result->num_rows === 0) {
      $error = 'No stock record found for this product. Please add stock first.';
    } else {
      $current_qty = $stock_result->fetch_assoc()['quantity'];
      
      if ($type == 'OUT' && $current_qty < $quantity) {
        $error = 'Insufficient stock. Current stock: ' . $current_qty;
      } else {
        $sql = "INSERT INTO stock_movement (product_id, type, quantity, note) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isis", $product_id, $type, $quantity, $note);
        
        if ($stmt->execute()) {
          if ($type == 'ADJUST') {
            $update_sql = "UPDATE stock SET quantity = ? WHERE product_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $quantity, $product_id);
            $update_stmt->execute();
          } else {
            updateStockQuantity($conn, $product_id, $type, $quantity);
          }
          $_SESSION['message'] = 'Stock movement recorded successfully!';
          header('Location: stock_movement.php');
          exit;
        } else {
          $error = 'Something went wrong. Please try again.';
        }
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
      <h3>Add Stock Movement</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="addstockmovement.php" method="POST" id="add-form" autocomplete="off">
        <div class="form-group">
          <label for="product_id">Product *</label>
          <select name="product_id" id="product_id" required>
            <option value="">Select Product</option>
            <?php foreach ($products as $prod): ?>
              <option value="<?= $prod['id'] ?>" <?= ($_POST['product_id'] ?? '') == $prod['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($prod['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="type">Type *</label>
          <select name="type" id="type" required>
            <option value="">Select Type</option>
            <option value="IN" <?= ($_POST['type'] ?? '') == 'IN' ? 'selected' : '' ?>>IN - Add Stock</option>
            <option value="OUT" <?= ($_POST['type'] ?? '') == 'OUT' ? 'selected' : '' ?>>OUT - Remove Stock</option>
            <option value="ADJUST" <?= ($_POST['type'] ?? '') == 'ADJUST' ? 'selected' : '' ?>>ADJUST - Set Exact Quantity</option>
          </select>
        </div>

        <div class="form-group">
          <label for="quantity">Quantity *</label>
          <input type="number" name="quantity" id="quantity" value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>" min="1" required>
          <small id="quantity-hint"></small>
        </div>

        <div class="form-group">
          <label for="note">Note</label>
          <textarea name="note" id="note" rows="3"><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
        </div>

        <div class="form-buttons">
          <button type="submit" class="submit-btn">Record Movement</button>
          <a href="stock_movement.php" class="cancel-btn">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const typeSelect = document.getElementById('type');
const quantityHint = document.getElementById('quantity-hint');

typeSelect.addEventListener('change', function() {
  if (this.value === 'ADJUST') {
    quantityHint.textContent = 'This will set the stock to this exact quantity.';
    quantityHint.style.color = '#ffc107';
  } else if (this.value === 'OUT') {
    quantityHint.textContent = 'This will remove this quantity from stock.';
    quantityHint.style.color = '#dc3545';
  } else if (this.value === 'IN') {
    quantityHint.textContent = 'This will add this quantity to stock.';
    quantityHint.style.color = '#28a745';
  } else {
    quantityHint.textContent = '';
  }
});
</script>

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
.form-group select,
.form-group textarea {
  width: 100%;
  max-width: 500px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
}
.form-group textarea {
  resize: vertical;
}
.form-group small {
  display: block;
  margin-top: 5px;
  font-size: 12px;
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
* label {
  color: #b2b2b2 !important;
}
</style>

<?php require('../components/footer.php'); ?>