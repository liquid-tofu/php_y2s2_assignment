<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$return_url = $_GET['return_url'] ?? ($_POST['return_url'] ?? 'products.php');

if ($id <= 0) {
  $_SESSION['message'] = 'Invalid product ID.';
  header("Location: $return_url");
  exit;
}

$sql = "SELECT id, name, `desc`, price, cost, cat_id FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['message'] = 'Product not found.';
  header("Location: $return_url");
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
        header("Location: $return_url");
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
      <i class="bi bi-person-circle"></i>
      <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
    </div>
  </div>

  <div class="content">
    <div id="content-container">
      <h3>Edit Product</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="editproduct.php?id=<?= $id ?>" method="POST" class="edit-form" autocomplete="off">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url) ?>">
        <div class="form-group">
          <label for="name">Product Name *</label>
          <input type="text" name="name" id="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div class="form-group">
          <label for="desc">Description</label>
          <input type="text" name="desc" id="desc" value="<?= htmlspecialchars($product['desc']) ?>">
        </div>

        <div class="form-group half">
          <label for="price">Price * ($)</label>
          <input type="number" step="0.01" name="price" id="price" value="<?= $product['price'] ?>" required>
        </div>

        <div class="form-group half">
          <label for="cost">Cost * ($)</label>
          <input type="number" step="0.01" name="cost" id="cost" value="<?= $product['cost'] ?>" required>
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
          <a href="<?= htmlspecialchars($return_url) ?>" id="cancel-btn">Cancel</a>
          <button type="submit" id="update-btn">Update Product</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require('../components/footer.php'); ?>