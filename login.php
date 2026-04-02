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
            setcookie('user_id', $user['id'], time() + 86400*30, "/"); // 30 days
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
    <style>
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #1c1822;
            font-family: "Inter Variable", sans-serif;
        }

        #login-container {
            background: #1c1822;
            border: 2px solid #00BFCB;
            border-radius: 5px;
            padding: 40px;
            width: 350px;
            color: #fff;
        }

        #login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #00BFCB;
        }

        .input-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        .input-group label {
            margin-bottom: 5px;
        }

        .input-group input {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #00BFCB;
            background: #1c1822;
            color: #fff;
        }

        .input-group input:focus {
            outline: none;
            border-color: #00BFCB;
        }

        label.remember {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            cursor: pointer;
        }

        label.remember input {
            margin-right: 10px;
        }

        button {
            width: 100%;
            padding: 10px;
            border: none;
            background-color: #00BFCB;
            color: #fff;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background-color: #009ea8;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            color: #e74c3c;
        }
    </style>
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