<?php
require_once 'components/header.php';
require_once 'components/sidebar.php';

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

<div class="main">
  <div class="topbar">
    <h3>Stock Management System</h3>
    <div class="user">
      <i class="bi bi-person-circle"></i> Administrator
    </div>
  </div>

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

<?php require_once 'components/footer.php'; ?>