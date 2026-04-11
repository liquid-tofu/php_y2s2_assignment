<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$return_url = $_GET['return_url'] ?? ($_POST['return_url'] ?? 'so.php');
if ($id <= 0) {
  $_SESSION['message'] = 'Invalid sales order ID.';
  header("Location: $return_url");
  exit;
}

$sql = "SELECT id, cus_name, cus_email, order_date, status, total_amount FROM so WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$so = $result ? $result->fetch_assoc() : null;

if (!$so) {
  $_SESSION['message'] = 'Sales order not found.';
  header("Location: $return_url");
  exit;
}

$products_result = $conn->query("SELECT id, name, price FROM products ORDER BY name");
$all_products = $products_result ? $products_result->fetch_all(MYSQLI_ASSOC) : [];
$product_map = [];
foreach ($all_products as $p) {
  $product_map[(int)$p['id']] = $p;
}

$items_stmt = $conn->prepare("SELECT product_id, quantity, unit_price FROM soi WHERE so_id = ? ORDER BY id ASC");
$items_stmt->bind_param("i", $id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = [];
if ($items_result) {
  while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
  }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $cus_name = trim($_POST['cus_name'] ?? '');
  $cus_email = trim($_POST['cus_email'] ?? '');
  $order_date = trim($_POST['order_date'] ?? '');
  $status = trim($_POST['status'] ?? '');
  $allowed = ['PENDING', 'APPROVED', 'REJECTED', 'CANCELLED'];
  $products = $_POST['products'] ?? [];
  $quantities = $_POST['quantities'] ?? [];
  $unit_prices = $_POST['unit_prices'] ?? [];
  $new_items = [];
  $total_amount = 0;

  if ($cus_name === '') {
    $error = 'Customer name is required.';
  } elseif (!filter_var($cus_email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid customer email.';
  } elseif ($order_date === '') {
    $error = 'Order date is required.';
  } elseif (!in_array($status, $allowed, true)) {
    $error = 'Invalid status.';
  } else {
    $current_items_stmt = $conn->prepare("SELECT product_id, quantity FROM soi WHERE so_id = ?");
    $current_items_stmt->bind_param("i", $id);
    $current_items_stmt->execute();
    $current_items_result = $current_items_stmt->get_result();
    $current_items = [];
    if ($current_items_result) {
      while ($r = $current_items_result->fetch_assoc()) {
        $current_items[] = $r;
      }
    }

    foreach ($products as $idx => $pid_raw) {
      $pid = (int)$pid_raw;
      $qty = (int)($quantities[$idx] ?? 0);
      $upr = (float)($unit_prices[$idx] ?? 0);
      if ($pid <= 0 && $qty <= 0 && $upr <= 0) {
        continue;
      }
      if ($pid <= 0 || $qty <= 0 || $upr <= 0) {
        $error = 'Each item row must have product, quantity and unit price.';
        break;
      }
      $new_items[] = ['product_id' => $pid, 'quantity' => $qty, 'unit_price' => $upr];
      $total_amount += ($qty * $upr);
    }

    if ($error === '' && count($new_items) === 0) {
      $error = 'Please add at least one item.';
    }

    if ($error === '') {
      $conn->begin_transaction();
      try {
        foreach ($current_items as $old) {
          $restock = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE product_id = ?");
          $restock->bind_param("ii", $old['quantity'], $old['product_id']);
          $restock->execute();
        }

        foreach ($new_items as $ni) {
          $check = $conn->prepare("SELECT quantity FROM stock WHERE product_id = ?");
          $check->bind_param("i", $ni['product_id']);
          $check->execute();
          $check_rs = $check->get_result();
          $available = 0;
          if ($check_rs && $check_rs->num_rows > 0) {
            $available = (int)$check_rs->fetch_assoc()['quantity'];
          }
          if ($available < (int)$ni['quantity']) {
            throw new Exception('Insufficient stock for selected product.');
          }
        }

        $up = $conn->prepare("UPDATE so SET cus_name = ?, cus_email = ?, order_date = ?, status = ?, total_amount = ? WHERE id = ?");
        $up->bind_param("ssssdi", $cus_name, $cus_email, $order_date, $status, $total_amount, $id);
        $up->execute();

        $del = $conn->prepare("DELETE FROM soi WHERE so_id = ?");
        $del->bind_param("i", $id);
        $del->execute();

        $ins = $conn->prepare("INSERT INTO soi (so_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        foreach ($new_items as $ni) {
          $ins->bind_param("iiid", $id, $ni['product_id'], $ni['quantity'], $ni['unit_price']);
          $ins->execute();
          $deduct = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE product_id = ?");
          $deduct->bind_param("ii", $ni['quantity'], $ni['product_id']);
          $deduct->execute();
        }

        $conn->commit();
        $_SESSION['message'] = 'Sales order updated successfully!';
        header("Location: $return_url");
        exit;
      } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
      }
    }
  }

  if ($error !== '') {
    $items = [];
    $row_count = max(count($products), 1);
    for ($i = 0; $i < $row_count; $i++) {
      $items[] = [
        'product_id' => (int)($products[$i] ?? 0),
        'quantity' => (int)($quantities[$i] ?? 1),
        'unit_price' => (float)($unit_prices[$i] ?? 0)
      ];
    }
    $so['cus_name'] = $cus_name;
    $so['cus_email'] = $cus_email;
    $so['order_date'] = $order_date;
    $so['status'] = $status;
    $so['total_amount'] = $total_amount;
  }
}

require('../components/header.php');
?>
<link rel="stylesheet" href="/styles/content.css">
<link rel="stylesheet" href="/styles/edit.css">
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
      <h3>Edit Sales Order</h3>
      <hr>
      <?php if ($error !== ''): ?>
        <div class="popup-message"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form action="editso.php?id=<?= $id ?>" method="POST" class="edit-form po-form" autocomplete="off">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url) ?>">
        <section class="po-section">
          <h4>Sale Info</h4>
          <div class="po-grid">
            <div>
              <label for="cus_name">Customer Name</label>
              <input type="text" name="cus_name" id="cus_name" value="<?= htmlspecialchars((string)$so['cus_name']) ?>" required>
            </div>
            <div>
              <label for="cus_email">Customer Email</label>
              <input type="email" name="cus_email" id="cus_email" value="<?= htmlspecialchars((string)$so['cus_email']) ?>" required>
            </div>
            <div>
              <label for="order_date">Order Date</label>
              <input type="date" name="order_date" id="order_date" value="<?= htmlspecialchars(substr((string)$so['order_date'], 0, 10)) ?>" required>
            </div>
            <div>
              <label for="status">Status</label>
              <select name="status" id="status" required>
                <?php foreach (['PENDING','APPROVED','REJECTED','CANCELLED'] as $st): ?>
                  <option value="<?= $st ?>" <?= (($so['status'] ?? '') === $st) ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </section>
        <section class="po-section">
          <h4>Item List</h4>
          <div id="items-container">
            <?php if (count($items) > 0): ?>
              <?php foreach ($items as $it): ?>
                <?php
                  $pid = (int)($it['product_id'] ?? 0);
                  $qty = (int)($it['quantity'] ?? 1);
                  $upr = (float)($it['unit_price'] ?? (($pid && isset($product_map[$pid])) ? (float)$product_map[$pid]['price'] : 0));
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
              <?php endforeach; ?>
            <?php else: ?>
              <div id="items-empty-message">No related items for this sales order.</div>
            <?php endif; ?>
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
          <a href="<?= htmlspecialchars($return_url) ?>" id="cancel-btn">Cancel</a>
          <button type="submit" id="update-btn">Update</button>
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
  const qtyInput = row.querySelector('.quantity');
  const unitPrice = row.querySelector('.unit-price');
  const removeBtn = row.querySelector('.remove-item');

  productSelect.addEventListener('change', () => {
    const selected = allProducts.find(p => String(p.id) === productSelect.value);
    if (selected && (!unitPrice.value || parseFloat(unitPrice.value) <= 0)) {
      unitPrice.value = parseFloat(selected.price).toFixed(2);
    }
    refreshTotal();
  });
  qtyInput.addEventListener('input', refreshTotal);
  unitPrice.addEventListener('input', refreshTotal);
  removeBtn.addEventListener('click', () => {
    if (document.querySelectorAll('.item-row').length <= 1) return;
    row.remove();
    refreshTotal();
  });
}

document.querySelectorAll('.item-row').forEach(bindRow);

document.getElementById('add-item').addEventListener('click', () => {
  const emptyMsg = document.getElementById('items-empty-message');
  if (emptyMsg) emptyMsg.remove();
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
