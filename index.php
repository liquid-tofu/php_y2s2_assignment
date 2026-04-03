<<<<<<< HEAD
<?php
// Sample data (replace with database later)
$data = [
    "PO Records" => 2,
    "Receiving Records" => 6,
    "BO Records" => 4,
    "Return Records" => 1,
    "Sales Records" => 1,
    "Suppliers" => 2,
    "Items" => 4,
    "Users" => 2
];

// Icon mapping
$icons = [
    "PO Records" => "bi-file-earmark-text",
    "Receiving Records" => "bi-box-arrow-in-down",
    "BO Records" => "bi-arrow-left-right",
    "Return Records" => "bi-arrow-counterclockwise",
    "Sales Records" => "bi-cash-stack",
    "Suppliers" => "bi-truck",
    "Items" => "bi-box-seam",
    "Users" => "bi-people"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Management Dashboard</title>

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">

    <!-- Logo -->
    <div class="logo">
        <img src="pics/Logo.jpg" alt="Logo">
        <span>Angkor Store</span>
    </div>

    <ul>
        <li class="active"><i class="bi bi-speedometer2"></i> Dashboard</li>
        <li><i class="bi bi-cart"></i> Purchase Order</li>
        <li><i class="bi bi-box-arrow-in-down"></i> Receiving</li>
        <li><i class="bi bi-arrow-left-right"></i> Back Order</li>
        <li><i class="bi bi-box-seam"></i> Stocks</li>
        <li><i class="bi bi-cash-stack"></i> Sales List</li>
        <li><i class="bi bi-truck"></i> Supplier List</li>
        <li><i class="bi bi-box"></i> Item List</li>
        <li><i class="bi bi-people"></i> User List</li>
        <li><i class="bi bi-gear"></i> Settings</li>
    </ul>
</div>

<!-- Main Content -->
<div class="main">

    <!-- Topbar -->
    <div class="topbar">
        <h3>Stock Management System</h3>
        <div class="user">
            <i class="bi bi-person-circle"></i> Administrator
        </div>
    </div>

    <!-- Page Content -->
    <div class="content">
        <h1>Welcome to Stock Management System </h1>
        <div class="cards">
            <?php foreach ($data as $title => $count): ?>
                <div class="card">
                    <div class="icon">
                        <i class="bi <?php echo $icons[$title]; ?>"></i>
                    </div>
                    <div class="card-title"><?php echo $title; ?></div>
                    <div class="card-count"><?php echo $count; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

</body>
</html>
=======
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
>>>>>>> e9ee9cd888dc6af1dc2484be2669258be56ae4df
