<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$return_url = $_GET['return_url'] ?? ($_POST['return_url'] ?? 'categories.php');
if ($id <= 0) {
  $_SESSION['message'] = 'Invalid category ID.';
  header("Location: $return_url");
  exit;
}

$stmt = $conn->prepare("SELECT id, name, `desc` FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result ? $result->fetch_assoc() : null;

if (!$category) {
  $_SESSION['message'] = 'Category not found.';
  header("Location: $return_url");
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $desc = trim($_POST['desc'] ?? '');

  if ($name === '') {
    $error = 'Category name is required.';
  } else {
    $check = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
    $check->bind_param("si", $name, $id);
    $check->execute();
    $check_result = $check->get_result();
    if ($check_result && $check_result->num_rows > 0) {
      $error = 'Category name already exists.';
    } else {
      $up = $conn->prepare("UPDATE categories SET name = ?, `desc` = ? WHERE id = ?");
      $up->bind_param("ssi", $name, $desc, $id);
      if ($up->execute()) {
        $_SESSION['message'] = 'Category updated successfully!';
        header("Location: $return_url");
        exit;
      }
      $error = 'Something went wrong. Please try again.';
    }
  }

  if ($error !== '') {
    $category['name'] = $name;
    $category['desc'] = $desc;
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
      <h3>Edit Category</h3>
      <hr>
      <?php if ($error !== ''): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form action="editcategory.php?id=<?= $id ?>" method="POST" class="edit-form" autocomplete="off">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url) ?>">
        <div class="form-group">
          <label for="name">Category Name *</label>
          <input type="text" name="name" id="name" value="<?= htmlspecialchars((string)$category['name']) ?>" required>
        </div>
        <div class="form-group">
          <label for="desc">Description</label>
          <input type="text" name="desc" id="desc" value="<?= htmlspecialchars((string)$category['desc']) ?>">
        </div>
        <div class="form-buttons">
          <a href="<?= htmlspecialchars($return_url) ?>" id="cancel-btn">Cancel</a>
          <button type="submit" id="update-btn">Update Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require('../components/footer.php'); ?>

