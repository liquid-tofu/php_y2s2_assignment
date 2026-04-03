<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
  $_SESSION['message'] = 'Invalid product ID.';
  header('Location: products.php');
  exit;
}

$sql = "SELECT id, name, `desc`, price, cost, cat_id FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['message'] = 'Product not found.';
  header('Location: products.php');
  exit;
}

$product = $result->fetch_assoc();

$error = '';

function getCategories($conn) {
  $sql = "SELECT id, name FROM categories ORDER BY name";
  $result = $conn->query($sql);
  return $result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $desc = trim($_POST['desc'] ?? '');
  $price = floatval($_POST['price'] ?? 0);
  $cost = floatval($_POST['cost'] ?? 0);
  $cat_id = intval($_POST['cat_id'] ?? 0);

  if (empty($name)) {
    $error = 'Product name is required.';
  } elseif ($price <= 0) {
    $error = 'Price must be greater than 0.';
  } elseif ($cost <= 0) {
    $error = 'Cost must be greater than 0.';
  } elseif ($cat_id <= 0) {
    $error = 'Please select a category.';
  } else {
    $check_sql = "SELECT id FROM products WHERE name = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $name, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
      $error = 'Product name already exists.';
    } else {
      $sql = "UPDATE products SET name = ?, `desc` = ?, price = ?, cost = ?, cat_id = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sddiii", $name, $desc, $price, $cost, $cat_id, $id);

      if ($stmt->execute()) {
        $_SESSION['message'] = 'Product updated successfully!';
        header('Location: products.php');
        exit;
      } else {
        $error = 'Something went wrong. Please try again.';
      }
    }
  }
}

$categories = getCategories($conn);

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
      <h3>Edit Product</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="editproduct.php?id=<?= $id ?>" method="POST" id="edit-form" autocomplete="off">
        <div class="form-group">
          <label for="name">Product Name *</label>
          <input type="text" name="name" id="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div class="form-group">
          <label for="desc">Description</label>
          <textarea name="desc" id="desc" rows="5"><?= htmlspecialchars($product['desc']) ?></textarea>
        </div>

        <div class="form-row">
          <div class="form-group half">
            <label for="price">Price * ($)</label>
            <input type="number" step="0.01" name="price" id="price" value="<?= $product['price'] ?>" required>
          </div>
          <div class="form-group half">
            <label for="cost">Cost * ($)</label>
            <input type="number" step="0.01" name="cost" id="cost" value="<?= $product['cost'] ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label for="cat_id">Category *</label>
          <select name="cat_id" id="cat_id" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= $product['cat_id'] == $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-buttons">
          <button type="submit" class="submit-btn">Update Product</button>
          <a href="products.php" class="cancel-btn">Cancel</a>
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
.form-row {
  display: flex;
  gap: 20px;
}
.form-group.half {
  flex: 1;
}
.form-group.half input {
  max-width: 100%;
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