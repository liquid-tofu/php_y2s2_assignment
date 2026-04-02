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
      <i class="bi bi-person-circle"></i> Administrator
    </div>
  </div>

  <div class="content">
    <div id="content-container">
      <h3>Add New Category</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="addcategory.php" method="POST" id="add-form" autocomplete="off">
        <div class="form-group">
          <label for="name">Category Name *</label>
          <input type="text" name="name" id="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="desc">Description</label>
          <textarea name="desc" id="desc" rows="5"><?= htmlspecialchars($_POST['desc'] ?? '') ?></textarea>
        </div>

        <div class="form-buttons">
          <button type="submit" class="submit-btn">Add Category</button>
          <a href="categories.php" class="cancel-btn">Cancel</a>
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