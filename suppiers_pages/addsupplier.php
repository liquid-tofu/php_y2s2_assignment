<?php
require('../db.php');
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $contact_person = trim($_POST['contact_person'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $address = trim($_POST['address'] ?? '');

  if (empty($name)) {
    $error = 'Supplier name is required.';
  } elseif (empty($email)) {
    $error = 'Email is required.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
  } elseif (empty($phone)) {
    $error = 'Phone number is required.';
  } else {
    $check_sql = "SELECT id FROM suppliers WHERE name = ? OR email = ? OR phone = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("sss", $name, $email, $phone);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
      $error = 'Supplier name, email or phone already exists.';
    } else {
      $sql = "INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sssss", $name, $contact_person, $email, $phone, $address);

      if ($stmt->execute()) {
        $_SESSION['message'] = 'Supplier added successfully!';
        header('Location: suppliers.php');
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
      <h3>Add New Supplier</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="addsupplier.php" method="POST" id="add-form" autocomplete="off">
        <div class="form-group">
          <label for="name">Supplier Name *</label>
          <input type="text" name="name" id="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="contact_person">Contact Person</label>
          <input type="text" name="contact_person" id="contact_person" value="<?= htmlspecialchars($_POST['contact_person'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="email">Email *</label>
          <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="phone">Phone *</label>
          <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="address">Address</label>
          <textarea name="address" id="address" rows="3"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
        </div>

        <div class="form-buttons">
          <button type="submit" class="submit-btn">Add Supplier</button>
          <a href="suppliers.php" class="cancel-btn">Cancel</a>
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