<?php
require('../db.php');
session_start();

$error = '';

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

function checkStock($conn, $product_id, $quantity) {
  $sql = "SELECT quantity FROM stock WHERE product_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $product_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result && $result->num_rows > 0) {
    $stock = $result->fetch_assoc();
    return $stock['quantity'] >= $quantity;
  }
  return false;
}

function updateStock($conn, $product_id, $quantity) {
  $sql = "UPDATE stock SET quantity = quantity - ? WHERE product_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $quantity, $product_id);
  return $stmt->execute();
}

$all_products = getProducts($conn);
$product_map = [];
foreach ($all_products as $p) {
  $product_map[(int)$p['id']] = $p;
}

$cus_name = trim($_POST['cus_name'] ?? '');
$cus_email = trim($_POST['cus_email'] ?? '');
$order_date = $_POST['order_date'] ?? date('Y-m-d');
$status = trim($_POST['status'] ?? 'PENDING');
$products = $_POST['products'] ?? [''];
$quantities = $_POST['quantities'] ?? [1];
$unit_prices = $_POST['unit_prices'] ?? [''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $allowed_status = ['PENDING', 'APPROVED', 'REJECTED', 'CANCELLED'];
  $items = [];
  $total_amount = 0;

  if (empty($cus_name)) {
    $error = 'Customer name is required.';
  } elseif (empty($cus_email)) {
    $error = 'Customer email is required.';
  } elseif (!filter_var($cus_email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
  } elseif (empty($order_date)) {
    $error = 'Please select order date.';
  } elseif (!in_array($status, $allowed_status, true)) {
    $error = 'Invalid status.';
  } else {
    foreach ($products as $index => $product_id_raw) {
      $product_id = (int)$product_id_raw;
      $quantity = (int)($quantities[$index] ?? 0);
      $unit_price = (float)($unit_prices[$index] ?? 0);

      if ($product_id <= 0 && $quantity <= 0 && $unit_price <= 0) {
        continue;
      }
      if ($product_id <= 0 || $quantity <= 0 || $unit_price <= 0) {
        $error = 'Each item row must have product, quantity and unit price.';
        break;
      }
      if (!checkStock($conn, $product_id, $quantity)) {
        $error = 'Insufficient stock for selected product.';
        break;
      }
      $items[] = [
        'product_id' => $product_id,
        'quantity' => $quantity,
        'unit_price' => $unit_price
      ];
      $total_amount += ($quantity * $unit_price);
    }

    if ($error === '' && count($items) === 0) {
      $error = 'Please add at least one product item.';
    } elseif ($error === '') {
      $conn->begin_transaction();
      try {
        $sql = "INSERT INTO so (cus_name, cus_email, order_date, status, total_amount) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssd", $cus_name, $cus_email, $order_date, $status, $total_amount);
        if (!$stmt->execute()) {
          throw new Exception('Failed to insert order');
        }

        $so_id = $conn->insert_id;

        $item_sql = "INSERT INTO soi (so_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);

        foreach ($items as $item) {
          $item_stmt->bind_param("iiid", $so_id, $item['product_id'], $item['quantity'], $item['unit_price']);
          if (!$item_stmt->execute()) {
            throw new Exception('Failed to insert order item');
          }
          updateStock($conn, $item['product_id'], $item['quantity']);
        }

        $conn->commit();
        $_SESSION['message'] = 'Sales order created successfully!';
        $_SESSION['message_type'] = 'success';
        header('Location: so.php');
        exit;
      } catch (Exception $e) {
        $conn->rollback();
        $error = 'Something went wrong: ' . $e->getMessage();
      }
    }
  }
}

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
      <h3>Create Sales Order</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="addso.php" method="POST" class="add-form po-form" autocomplete="off">
        <section class="po-section">
          <h4>Sale Info</h4>
          <div class="po-grid">
            <div>
              <label for="cus_name">Customer Name</label>
              <input type="text" name="cus_name" id="cus_name" value="<?= htmlspecialchars($cus_name) ?>" required>
            </div>
            <div>
              <label for="cus_email">Customer Email</label>
              <input type="email" name="cus_email" id="cus_email" value="<?= htmlspecialchars($cus_email) ?>" required>
            </div>
            <div>
              <label for="order_date">Order Date</label>
              <input type="date" name="order_date" id="order_date" value="<?= htmlspecialchars($order_date) ?>" required>
            </div>
            <div>
              <label for="status">Status</label>
              <select name="status" id="status" required>
                <?php foreach (['PENDING', 'APPROVED', 'REJECTED', 'CANCELLED'] as $st): ?>
                  <option value="<?= $st ?>" <?= ($status === $st) ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </section>
        <section class="po-section">
          <h4>Item List</h4>
          <?php $row_count = max(count($products), 1); ?>
          <div id="items-container">
            <?php for ($i = 0; $i < $row_count; $i++): ?>
              <?php
                $pid = (int)($products[$i] ?? 0);
                $qty = (int)($quantities[$i] ?? 1);
                $upr = (float)($unit_prices[$i] ?? (($pid && isset($product_map[$pid])) ? (float)$product_map[$pid]['price'] : 0));
              ?>
              <div class="item-row">
                <div class="item-col item-product">
                  <label>Product</label>
                  <select name="products[]" class="product-select" required>
                    <option value="">Select Product</option>
                    <?php foreach ($all_products as $prod): ?>
                      <option value="<?= (int)$prod['id'] ?>" data-price="<?= htmlspecialchars((string)$prod['price']) ?>" <?= ($pid === (int)$prod['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($prod['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="item-col">
                  <label>Qty</label>
                  <input type="number" name="quantities[]" class="quantity" min="1" value="<?= max($qty, 1) ?>" required>
                </div>
                <div class="item-col">
                  <label>Unit Price</label>
                  <input type="number" step="0.01" name="unit_prices[]" class="unit-price" min="0.01" value="<?= $upr > 0 ? htmlspecialchars(number_format($upr, 2, '.', '')) : '' ?>" required>
                </div>
                <div class="item-col">
                  <label>Row Total</label>
                  <input type="text" class="item-total" readonly>
                </div>
                <div class="item-col item-action">
                  <label>&nbsp;</label>
                  <button type="button" class="remove-item">Remove</button>
                </div>
              </div>
            <?php endfor; ?>
          </div>
          <template id="item-row-template">
            <div class="item-row">
              <div class="item-col item-product">
                <label>Product</label>
                <select name="products[]" class="product-select" required>
                  <option value="">Select Product</option>
                  <?php foreach ($all_products as $prod): ?>
                    <option value="<?= (int)$prod['id'] ?>" data-price="<?= htmlspecialchars((string)$prod['price']) ?>">
                      <?= htmlspecialchars($prod['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="item-col">
                <label>Qty</label>
                <input type="number" name="quantities[]" class="quantity" min="1" value="1" required>
              </div>
              <div class="item-col">
                <label>Unit Price</label>
                <input type="number" step="0.01" name="unit_prices[]" class="unit-price" min="0.01" value="" required>
              </div>
              <div class="item-col">
                <label>Row Total</label>
                <input type="text" class="item-total" readonly>
              </div>
              <div class="item-col item-action">
                <label>&nbsp;</label>
                <button type="button" class="remove-item">Remove</button>
              </div>
            </div>
          </template>
          <button type="button" id="add-item" class="po-add-item-btn">+ Add Item</button>
        </section>
        <section class="po-summary">
          <label>Total Value</label>
          <input type="text" id="grand-total" readonly value="$0.00">
        </section>
        <section>
          <a href="so.php" id="cancel-btn">Cancel</a>
          <button type="submit" id="add-btn">Create Order</button>
        </section>
      </form>
    </div>
  </div>
</div>

<script>
const allProducts = <?= json_encode($all_products) ?>;
const itemsContainer = document.getElementById('items-container');
const itemTemplate = document.getElementById('item-row-template');

function rowTotal(row) {
  const qty = parseFloat(row.querySelector('.quantity').value) || 0;
  const price = parseFloat(row.querySelector('.unit-price').value) || 0;
  const total = qty * price;
  row.querySelector('.item-total').value = '$' + total.toFixed(2);
  return total;
}

function refreshTotal() {
  const rows = document.querySelectorAll('.item-row');
  let sum = 0;
  rows.forEach(row => {
    sum += rowTotal(row);
    const removeBtn = row.querySelector('.remove-item');
    removeBtn.style.display = rows.length > 1 ? 'inline-block' : 'none';
  });
  document.getElementById('grand-total').value = '$' + sum.toFixed(2);
}

function bindRow(row) {
  const productSelect = row.querySelector('.product-select');
  const quantity = row.querySelector('.quantity');
  const unitPrice = row.querySelector('.unit-price');
  const removeBtn = row.querySelector('.remove-item');

  productSelect.addEventListener('change', () => {
    const selected = allProducts.find(p => String(p.id) === productSelect.value);
    if (selected) {
      if (!unitPrice.value || parseFloat(unitPrice.value) <= 0) {
        unitPrice.value = parseFloat(selected.price).toFixed(2);
      }
    }
    refreshTotal();
  });
  quantity.addEventListener('input', refreshTotal);
  unitPrice.addEventListener('input', refreshTotal);
  removeBtn.addEventListener('click', () => {
    if (document.querySelectorAll('.item-row').length <= 1) return;
    row.remove();
    refreshTotal();
  });
}

document.querySelectorAll('.item-row').forEach(bindRow);

document.getElementById('add-item').addEventListener('click', () => {
  const first = document.querySelector('.item-row');
  const clone = first ? first.cloneNode(true) : itemTemplate.content.firstElementChild.cloneNode(true);
  clone.querySelector('.product-select').value = '';
  clone.querySelector('.quantity').value = '1';
  clone.querySelector('.unit-price').value = '';
  clone.querySelector('.item-total').value = '$0.00';
  itemsContainer.appendChild(clone);
  bindRow(clone);
  refreshTotal();
});

refreshTotal();
</script>

<?php require('../components/footer.php'); ?>