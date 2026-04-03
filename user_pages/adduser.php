<?php
require('../db.php');
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  $role = $_POST['role'] ?? 'STAFF';

  if (empty($username) || empty($email) || empty($password)) {
    $error = 'Please fill in all required fields.';
  } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
    $error = 'Please enter a valid email address. (example: name@domain.com)';
  } elseif (strlen($password) < 6) {
    $error = 'Password must be at least 6 characters.';
  } elseif ($password !== $confirm_password) {
    $error = 'Passwords do not match.';
  } else {
    $allowed_roles = ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'];
    $role = in_array($role, $allowed_roles) ? $role : 'STAFF';

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
      $error = 'Username or email already exists.';
    } else {
      $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

      if ($stmt->execute()) {
        $_SESSION['message'] = 'User added successfully!';
        header('Location: user.php');
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
      <h3>Add New User</h3>
      <hr>

      <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="adduser.php" method="POST" id="add-form" autocomplete="off">
        <div class="form-group">
          <label for="username">Username *</label>
          <input type="text" name="username" id="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="off" required>
        </div>

        <div class="form-group">
          <label for="email">Email *</label>
          <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autocomplete="off" required>
        </div>

        <div class="form-group">
          <label for="password">Password * (min 6 characters)</label>
          <input type="password" name="password" id="password" autocomplete="new-password" required>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm Password *</label>
          <input type="password" name="confirm_password" id="confirm_password" autocomplete="off" required>
        </div>

        <div class="form-group">
          <label for="role">Role</label>
          <select name="role" id="role">
            <option value="STAFF" <?= ($_POST['role'] ?? 'STAFF') == 'STAFF' ? 'selected' : '' ?>>STAFF</option>
            <option value="VIEWER" <?= ($_POST['role'] ?? '') == 'VIEWER' ? 'selected' : '' ?>>VIEWER</option>
            <option value="MANAGER" <?= ($_POST['role'] ?? '') == 'MANAGER' ? 'selected' : '' ?>>MANAGER</option>
            <option value="ADMIN" <?= ($_POST['role'] ?? '') == 'ADMIN' ? 'selected' : '' ?>>ADMIN</option>
          </select>
        </div>

        <div class="form-buttons">
          <button type="submit" class="submit-btn">Add User</button>
          <a href="user.php" class="cancel-btn">Cancel</a>
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
.form-group select {
  width: 100%;
  max-width: 400px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
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