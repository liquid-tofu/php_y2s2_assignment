<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
  $_SESSION['user_id'] = $_COOKIE['user_id'];
  $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->bind_param("i", $_SESSION['user_id']);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  if ($user) {
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
  } else {
    setcookie('user_id', '', time() - 3600, "/");
    header("Location: login.php");
    exit;
  }
}

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

require_once 'components/header.php';
require_once 'components/sidebar.php';

function countTable($conn, $table) {
  $result = $conn->query("SELECT COUNT(*) as total FROM $table");
  return $result->fetch_assoc()['total'] ?? 0;
}

$poCount = countTable($conn, 'po');
$soCount = countTable($conn, 'so');
$supplierCount = countTable($conn, 'suppliers');
$productCount = countTable($conn, 'products');
$userCount = countTable($conn, 'users');
$stockCount = countTable($conn, 'stock');
$movementCount = countTable($conn, 'stock_movement');
$categoryCount = countTable($conn, 'categories');

$data = [
  "Purchase Orders" => $poCount,
  "Sales Orders" => $soCount,
  "Suppliers" => $supplierCount,
  "Products" => $productCount,
  "Users" => $userCount,
  "Stock Items" => $stockCount,
  "Movements" => $movementCount,
  "Categories" => $categoryCount
];

$icons = [
  "Purchase Orders" => "bi-file-earmark-text",
  "Sales Orders" => "bi-cash-stack",
  "Suppliers" => "bi-truck",
  "Products" => "bi-box-seam",
  "Users" => "bi-people",
  "Stock Items" => "bi-box",
  "Movements" => "bi-arrow-left-right",
  "Categories" => "bi-tags"
];
?>

<div class="main">
  <div class="topbar">
    <h3>Stock Management System</h3>
    <div class="user">
      <i class="bi bi-person-circle"></i> <?= $_SESSION['username'] ?>
    </div>
  </div>

  <div class="content">
    <h1>Dashboard</h1>
    <div class="cards">
      <?php foreach ($data as $title => $count): ?>
        <div class="card">
          <div class="icon">
            <i class="bi <?= $icons[$title] ?>"></i>
          </div>
          <div class="card-title"><?= $title ?></div>
          <div class="card-count"><?= $count ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require_once 'components/footer.php'; ?>