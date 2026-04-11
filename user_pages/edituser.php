<?php
require('../db.php');
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$return_url = $_GET['return_url'] ?? ($_POST['return_url'] ?? 'user.php');

if ($id <= 0) {
  $_SESSION['message'] = 'Invalid user ID.';
  header("Location: $return_url");
  exit;
}

$sql = "SELECT id, username, email, role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['message'] = 'User not found.';
  header("Location: $return_url");
  exit;
}

$user = $result->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $role = $_POST['role'] ?? 'STAFF';
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if (empty($username) || empty($email)) {
    $error = 'Username and email are required.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
  } elseif (!empty($password) && strlen($password) < 6) {
    $error = 'Password must be at least 6 characters.';
  } elseif (!empty($password) && $password !== $confirm_password) {
    $error = 'Passwords do not match.';
  } else {
    $allowed_roles = ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'];
    $role = in_array($role, $allowed_roles) ? $role : 'STAFF';

    $check_sql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ssi", $username, $email, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
      $error = 'Username or email already exists.';
    } else {
      if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $username, $email, $hashed_password, $role, $id);
      } else {
        $sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $email, $role, $id);
      }

      if ($stmt->execute()) {
        $_SESSION['message'] = 'User updated successfully!';
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
      <h3>Edit User</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="edituser.php?id=<?= $id ?>" method="POST" class="edit-form">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url) ?>">
        <div class="form-group">
          <label for="username">Username *</label>
          <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
          <label for="email">Email *</label>
          <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="form-group">
          <label for="password">New Password (leave blank to keep current)</label>
          <input type="password" name="password" id="password">
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm New Password</label>
          <input type="password" name="confirm_password" id="confirm_password">
        </div>

        <div class="form-group">
          <label for="role">Role</label>
          <select name="role" id="role">
            <option value="STAFF" <?= $user['role'] == 'STAFF' ? 'selected' : '' ?>>STAFF</option>
            <option value="VIEWER" <?= $user['role'] == 'VIEWER' ? 'selected' : '' ?>>VIEWER</option>
            <option value="MANAGER" <?= $user['role'] == 'MANAGER' ? 'selected' : '' ?>>MANAGER</option>
            <option value="ADMIN" <?= $user['role'] == 'ADMIN' ? 'selected' : '' ?>>ADMIN</option>
          </select>
        </div>

        <div class="form-buttons">
          <a href="<?= htmlspecialchars($return_url) ?>" id="cancel-btn">Cancel</a>
          <button type="submit" id="update-btn">Update User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require('../components/footer.php'); ?>