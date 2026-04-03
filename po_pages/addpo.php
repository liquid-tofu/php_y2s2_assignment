<?php
require('../db.php');
session_start();

$error = '';
$products_list = [];

function getSuppliers($conn) {
  $sql = "SELECT id, name FROM suppliers ORDER BY name";
  $result = $conn->query($sql);
  return $result->fetch_all(MYSQLI_ASSOC);
}

function getProducts($conn) {
  $sql = "SELECT id, name, price FROM products ORDER BY name";
  $result = $conn->query($sql);
  return $result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $supplier_id = intval($_POST['supplier_id'] ?? 0);
  $order_date = $_POST['order_date'] ?? '';
  $products = $_POST['products'] ?? [];
  $quantities = $_POST['quantities'] ?? [];
  $unit_prices = $_POST['unit_prices'] ?? [];

  if ($supplier_id <= 0) {
    $error = 'Please select a supplier.';
  } elseif (empty($order_date)) {
    $error = 'Please select order date.';
  } else {
    $has_product = false;
    $total_amount = 0;
    $items = [];

    foreach ($products as $index => $product_id) {
      $quantity = intval($quantities[$index] ?? 0);
      $unit_price = floatval($unit_prices[$index] ?? 0);
      if ($product_id && $quantity > 0 && $unit_price > 0) {
        $has_product = true;
        $item_total = $quantity * $unit_price;
        $total_amount += $item_total;
        $items[] = [
          'product_id' => $product_id,
          'quantity' => $quantity,
          'unit_price' => $unit_price
        ];
      }
    }

    if (!$has_product) {
      $error = 'Please add at least one product item.';
    } else {
      $conn->begin_transaction();
      try {
        $sql = "INSERT INTO po (supplier_id, order_date, total_amount) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isd", $supplier_id, $order_date, $total_amount);
        $stmt->execute();
        $po_id = $conn->insert_id;

        $item_sql = "INSERT INTO poi (po_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);
        foreach ($items as $item) {
          $item_stmt->bind_param("iiid", $po_id, $item['product_id'], $item['quantity'], $item['unit_price']);
          $item_stmt->execute();
        }

        $conn->commit();
        $_SESSION['message'] = 'Purchase order created successfully!';
        header('Location: po.php');
        exit;
      } catch (Exception $e) {
        $conn->rollback();
        $error = 'Something went wrong. Please try again.';
      }
    }
  }
}

$suppliers = getSuppliers($conn);
$all_products = getProducts($conn);

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
      <h3>Create Purchase Order</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="addpo.php" method="POST" id="add-form" autocomplete="off">
        <div class="form-group">
          <label for="supplier_id">Supplier *</label>
          <select name="supplier_id" id="supplier_id" required>
            <option value="">Select Supplier</option>
            <?php foreach ($suppliers as $sup): ?>
              <option value="<?= $sup['id'] ?>" <?= ($_POST['supplier_id'] ?? '') == $sup['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($sup['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="order_date">Order Date *</label>
          <input type="date" name="order_date" id="order_date" value="<?= htmlspecialchars($_POST['order_date'] ?? date('Y-m-d')) ?>" required>
        </div>

        <h4>Order Items</h4>
        <div id="items-container">
          <div class="item-row">
            <div class="form-group half">
              <label>Product</label>
              <select name="products[]" class="product-select" required>
                <option value="">Select Product</option>
                <?php foreach ($all_products as $prod): ?>
                  <option value="<?= $prod['id'] ?>" data-price="<?= $prod['price'] ?>">
                    <?= htmlspecialchars($prod['name']) ?> - $<?= number_format($prod['price'], 2) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group half">
              <label>Quantity</label>
              <input type="number" name="quantities[]" class="quantity" min="1" value="1">
            </div>
            <div class="form-group half">
              <label>Unit Price</label>
              <input type="number" step="0.01" name="unit_prices[]" class="unit-price" min="0.01">
            </div>
            <div class="form-group half">
              <label>Total</label>
              <input type="text" class="item-total" readonly>
            </div>
            <button type="button" class="remove-item" style="display:none;">Remove</button>
          </div>
        </div>
        <button type="button" id="add-item" class="secondary-btn">+ Add Item</button>

        <div class="form-group total-group">
          <label>Grand Total:</label>
          <span id="grand-total">$0.00</span>
        </div>

        <div class="form-buttons">
          <button type="submit" class="submit-btn">Create Order</button>
          <a href="po.php" class="cancel-btn">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const allProducts = <?php echo json_encode($all_products); ?>;

function calculateItemTotal(row) {
  const qty = parseFloat(row.querySelector('.quantity').value) || 0;
  const price = parseFloat(row.querySelector('.unit-price').value) || 0;
  const total = qty * price;
  row.querySelector('.item-total').value = '$' + total.toFixed(2);
  return total;
}

function calculateGrandTotal() {
  let grand = 0;
  document.querySelectorAll('.item-row').forEach(row => {
    grand += calculateItemTotal(row);
  });
  document.getElementById('grand-total').textContent = '$' + grand.toFixed(2);
}

document.getElementById('add-item').addEventListener('click', function() {
  const container = document.getElementById('items-container');
  const originalRow = container.querySelector('.item-row');
  const newRow = originalRow.cloneNode(true);
  newRow.querySelectorAll('input, select').forEach(el => {
    if (el.classList.contains('product-select')) el.value = '';
    if (el.classList.contains('quantity')) el.value = '1';
    if (el.classList.contains('unit-price')) el.value = '';
    if (el.classList.contains('item-total')) el.value = '';
  });
  newRow.querySelector('.remove-item').style.display = 'inline-block';
  container.appendChild(newRow);
  attachRowEvents(newRow);
});

function attachRowEvents(row) {
  const productSelect = row.querySelector('.product-select');
  const unitPrice = row.querySelector('.unit-price');
  const quantity = row.querySelector('.quantity');

  productSelect.addEventListener('change', function() {
    const selected = allProducts.find(p => p.id == this.value);
    if (selected) {
      unitPrice.value = selected.price;
      calculateItemTotal(row);
      calculateGrandTotal();
    }
  });

  quantity.addEventListener('input', function() {
    calculateItemTotal(row);
    calculateGrandTotal();
  });

  unitPrice.addEventListener('input', function() {
    calculateItemTotal(row);
    calculateGrandTotal();
  });

  const removeBtn = row.querySelector('.remove-item');
  if (removeBtn) {
    removeBtn.addEventListener('click', function() {
      row.remove();
      calculateGrandTotal();
    });
  }
}

document.querySelectorAll('.item-row').forEach(row => attachRowEvents(row));
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
.form-group select {
  width: 100%;
  max-width: 400px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}
.item-row {
  display: flex;
  gap: 15px;
  align-items: flex-end;
  margin-bottom: 15px;
  flex-wrap: wrap;
}
.item-row .form-group {
  margin-bottom: 0;
  flex: 1;
}
.item-row .form-group.half {
  flex: 1;
  min-width: 120px;
}
.item-row .form-group.half input,
.item-row .form-group.half select {
  max-width: 100%;
}
.remove-item {
  background: #dc3545;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 4px;
  cursor: pointer;
  height: 38px;
}
.secondary-btn {
  background: #6c757d;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-bottom: 20px;
}
.secondary-btn:hover {
  background: #5a6268;
}
.total-group {
  margin-top: 20px;
  padding-top: 15px;
  border-top: 2px solid #ddd;
  font-size: 18px;
  font-weight: bold;
}
#grand-total {
  color: #00BFCB;
  font-size: 22px;
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