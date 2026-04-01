<?php
// ================= DATABASE CONNECTION =================
$conn = new mysqli("localhost", "root", "", "stock_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ================= FUNCTION TO GET COUNT =================
function getCount($conn, $table) {
    $result = $conn->query("SELECT COUNT(*) as total FROM $table");

    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    } else {
        return 0; // if table doesn't exist or error
    }
}

// ================= DYNAMIC DATA =================
$data = [
    "PO Records" => getCount($conn, "purchase_orders"),
    "Receiving Records" => getCount($conn, "receiving"),
    "BO Records" => getCount($conn, "back_orders"),
    "Return Records" => getCount($conn, "returns"),
    "Sales Records" => getCount($conn, "sales"),
    "Suppliers" => getCount($conn, "suppliers"),
    "Items" => getCount($conn, "items"),
    "Users" => getCount($conn, "users")
];

// ================= ICONS =================
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

<!-- ================= SIDEBAR ================= -->
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

<!-- ================= MAIN ================= -->
<div class="main">

    <!-- Topbar -->
    <div class="topbar">
        <h3>Stock Management System</h3>
        <div class="user">
            <i class="bi bi-person-circle"></i> Administrator
        </div>
    </div>

    <!-- Content -->
    <div class="content">
        <h1>Welcome to Stock Management System</h1>

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
