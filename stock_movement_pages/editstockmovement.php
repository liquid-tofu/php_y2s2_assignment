<?php
require('../db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../login.php');
  exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$return_url = $_GET['return_url'] ?? ($_POST['return_url'] ?? 'stock_movement.php');

if ($id <= 0) {
  $_SESSION['message'] = 'Invalid movement ID.';
  header("Location: $return_url");
  exit;
}

$sql = "SELECT sm.id, sm.product_id, sm.type, sm.quantity, sm.note, p.name as product_name 
        FROM stock_movement sm 
        JOIN products p ON sm.product_id = p.id 
        WHERE sm.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['message'] = 'Stock movement record not found.';
  header("Location: $return_url");
  exit;
}

$movement = $result->fetch_assoc();

$error = '';

function getProducts($conn) {
  $sql = "SELECT id, name FROM products ORDER BY name";
  $result = $conn->query($sql);
  return $result->fetch_all(MYSQLI_ASSOC);
}

function revertStockQuantity($conn, $product_id, $old_type, $old_qty) {
  if ($old_type == 'IN') {
    $sql = "UPDATE stock SET quantity = quantity - ? WHERE product_id = ?";
  } elseif ($old_type == 'OUT') {
    $sql = "UPDATE stock SET quantity = quantity + ? WHERE product_id = ?";
  } else {
    $sql = "SELECT quantity FROM stock WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();
    return $current['quantity'];
  }
  
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $old_qty, $product_id);
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
    revertStockQuantity($conn, $movement['product_id'], $movement['type'], $movement['quantity']);
    
    $check_stock_sql = "SELECT quantity FROM stock WHERE product_id = ?";
    $check_stock_stmt = $conn->prepare($check_stock_sql);
    $check_stock_stmt->bind_param("i", $product_id);
    $check_stock_stmt->execute();
    $stock_result = $check_stock_stmt->get_result();
    $current_qty = $stock_result->fetch_assoc()['quantity'];
    
    if ($type == 'OUT' && $current_qty < $quantity) {
      $error = 'Insufficient stock. Current stock: ' . $current_qty;
    } else {
      $sql = "UPDATE stock_movement SET product_id = ?, type = ?, quantity = ?, note = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("isisi", $product_id, $type, $quantity, $note, $id);
      
      if ($stmt->execute()) {
        if ($type == 'ADJUST') {
          $update_sql = "UPDATE stock SET quantity = ? WHERE product_id = ?";
          $update_stmt = $conn->prepare($update_sql);
          $update_stmt->bind_param("ii", $quantity, $product_id);
          $update_stmt->execute();
        } else {
          if ($type == 'IN') {
            $update_sql = "UPDATE stock SET quantity = quantity + ? WHERE product_id = ?";
          } else {
            $update_sql = "UPDATE stock SET quantity = quantity - ? WHERE product_id = ?";
          }
          $update_stmt = $conn->prepare($update_sql);
          $update_stmt->bind_param("ii", $quantity, $product_id);
          $update_stmt->execute();
        }
        $_SESSION['message'] = 'Stock movement updated successfully!';
        header("Location: $return_url");
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
      <i class="bi bi-person-circle"></i>
      <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
    </div>
  </div>

  <div class="content">
    <div id="content-container">
      <h3>Edit Stock Movement</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="editstockmovement.php?id=<?= $id ?>" method="POST" class="edit-form" autocomplete="off">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url) ?>">
        <div class="form-group">
          <label for="product_id">Product *</label>
          <select name="product_id" id="product_id" required>
            <option value="">Select Product</option>
            <?php foreach ($products as $prod): ?>
              <option value="<?= $prod['id'] ?>" <?= $movement['product_id'] == $prod['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($prod['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="type">Type *</label>
          <select name="type" id="type" required>
            <option value="">Select Type</option>
            <option value="IN" <?= $movement['type'] == 'IN' ? 'selected' : '' ?>>IN - Add Stock</option>
            <option value="OUT" <?= $movement['type'] == 'OUT' ? 'selected' : '' ?>>OUT - Remove Stock</option>
            <option value="ADJUST" <?= $movement['type'] == 'ADJUST' ? 'selected' : '' ?>>ADJUST - Set Exact Quantity</option>
          </select>
        </div>

        <div class="form-group">
          <label for="quantity">Quantity *</label>
          <input type="number" name="quantity" id="quantity" value="<?= $movement['quantity'] ?>" min="1" required>
          <small id="quantity-hint"></small>
        </div>

        <div class="form-group">
          <label for="note">Note</label>
          <input type="text" name="note" id="note" value="<?= htmlspecialchars($movement['note']) ?>">
        </div>

        <div class="form-buttons">
          <a href="<?= htmlspecialchars($return_url) ?>" id="cancel-btn">Cancel</a>
          <button type="submit" id="update-btn">Update Movement</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const typeSelect = document.getElementById('type');
const quantityHint = document.getElementById('quantity-hint');

// Trigger on page load
function updateHint() {
  if (typeSelect.value === 'ADJUST') {
    quantityHint.textContent = 'This will set the stock to this exact quantity.';
    quantityHint.style.color = '#ffc107';
  } else if (typeSelect.value === 'OUT') {
    quantityHint.textContent = 'This will remove this quantity from stock.';
    quantityHint.style.color = '#dc3545';
  } else if (typeSelect.value === 'IN') {
    quantityHint.textContent = 'This will add this quantity to stock.';
    quantityHint.style.color = '#28a745';
  } else {
    quantityHint.textContent = '';
  }
}

// Run on page load
updateHint();

// Run when selection changes
typeSelect.addEventListener('change', updateHint);
</script>

<?php require('../components/footer.php'); ?>