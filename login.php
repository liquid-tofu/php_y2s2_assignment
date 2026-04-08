<?php
session_start();
require_once 'db.php';

$message = '';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
  $user_id = $_COOKIE['user_id'];
  
  $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if ($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    header("Location: index.php");
    exit;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  $remember = isset($_POST['remember_me']);

  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    if ($remember) {
      setcookie('user_id', $user['id'], time() + 86400*30, "/");
    }

    header("Location: index.php");
    exit;
  } else {
    $message = "Invalid username or password";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles/login.css">
</head>
<body>
  <div id="login-container">
    <h2>Login</h2>
    <?php if ($message): ?>
      <div class="message"><?= $message ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="input-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
      </div>
      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
      </div>
      <label class="remember">
        <input type="checkbox" name="remember_me"> Remember Me
      </label>
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
