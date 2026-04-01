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