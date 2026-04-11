<?php
require('../db.php');
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $desc = trim($_POST['desc'] ?? '');

  if (empty($name)) {
    $error = 'Category name is required.';
  } else {
    $check_sql = "SELECT id FROM categories WHERE name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
      $error = 'Category name already exists.';
    } else {
      $sql = "INSERT INTO categories (name, `desc`) VALUES (?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ss", $name, $desc);

      if ($stmt->execute()) {
        $_SESSION['message'] = 'Category added successfully!';
        header('Location: categories.php');
        exit;
      } else {
        $error = 'Something went wrong. Please try again.';
      }
    }
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
      <i class="bi bi-person-circle"></i>
      <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
    </div>
  </div>

  <div class="content">
    <div id="content-container">
      <h3>Add New Category</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="addcategory.php" method="POST" class="add-form" autocomplete="off">
        <div class="form-group">
          <label for="name">Category Name *</label>
          <input type="text" name="name" id="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="desc">Description</label>
          <input type="text" name="desc" id="desc" value="<?= htmlspecialchars($_POST['desc'] ?? '') ?>">
        </div>

        <div class="form-buttons">
          <a href="categories.php" id="cancel-btn">Cancel</a>
          <button type="submit" id="add-btn">Add Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require('../components/footer.php'); ?>