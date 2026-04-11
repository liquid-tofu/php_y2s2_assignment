<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$return_url = $_GET['return_url'] ?? ($_POST['return_url'] ?? 'suppliers.php');

if ($id <= 0) {
  $_SESSION['message'] = 'Invalid supplier ID.';
  header("Location: $return_url");
  exit;
}

$sql = "SELECT id, name, contact_person, email, phone, address FROM suppliers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['message'] = 'Supplier not found.';
  header("Location: $return_url");
  exit;
}

$supplier = $result->fetch_assoc();

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
    $check_sql = "SELECT id FROM suppliers WHERE (name = ? OR email = ? OR phone = ?) AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("sssi", $name, $email, $phone, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
      $error = 'Supplier name, email or phone already exists.';
    } else {
      $sql = "UPDATE suppliers SET name = ?, contact_person = ?, email = ?, phone = ?, address = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sssssi", $name, $contact_person, $email, $phone, $address, $id);

      if ($stmt->execute()) {
        $_SESSION['message'] = 'Supplier updated successfully!';
        header("Location: $return_url");
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
      <h3>Edit Supplier</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="editsupplier.php?id=<?= $id ?>" method="POST" class="edit-form" autocomplete="off">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url) ?>">
        <div class="form-group">
          <label for="name">Supplier Name *</label>
          <input type="text" name="name" id="name" value="<?= htmlspecialchars($supplier['name']) ?>" required>
        </div>

        <div class="form-group">
          <label for="contact_person">Contact Person</label>
          <input type="text" name="contact_person" id="contact_person" value="<?= htmlspecialchars($supplier['contact_person']) ?>">
        </div>

        <div class="form-group">
          <label for="email">Email *</label>
          <input type="email" name="email" id="email" value="<?= htmlspecialchars($supplier['email']) ?>" required>
        </div>

        <div class="form-group">
          <label for="phone">Phone *</label>
          <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($supplier['phone']) ?>" required>
        </div>

        <div class="form-group">
          <label for="address">Address</label>
          <input type="text" name="address" id="address" value="<?= htmlspecialchars($supplier['address']) ?>">
        </div>

        <div class="form-buttons">
          <a href="<?= htmlspecialchars($return_url) ?>" id="cancel-btn">Cancel</a>
          <button type="submit" id="update-btn">Update Supplier</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require('../components/footer.php'); ?>