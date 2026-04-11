<?php
$cur = basename($_SERVER['PHP_SELF']);
$is_active = fn($pages) => in_array($cur, (array)$pages) ? 'active' : '';
?>

<div class="sidebar">
  <div class="logo">
    <img src="/resources/Logo.svg" alt="Logo">
  </div>
  <ul>
    <li class="<?= $is_active('index.php') ?>">
      <a href="/index.php"><i class="bi bi-speedometer2"></i> <span class="sidebar-link">Dashboard</span></a>
    </li>
    <li class="<?= $is_active(['user.php', 'edituser.php', 'adduser.php']) ?>">
      <a href="/user_pages/user.php"><i class="bi bi-people"></i> <span class="sidebar-link">Users</span></a>
    </li>
    <li class="<?= $is_active(['products.php', 'editproduct.php', 'addproduct.php']) ?>">
      <a href="/products_pages/products.php"><i class="bi bi-box-seam"></i> <span class="sidebar-link">Products</span></a>
    </li>
    <li class="<?= $is_active(['suppliers.php', 'editsupplier.php', 'addsupplier.php']) ?>">
      <a href="/suppiers_pages/suppliers.php"><i class="bi bi-truck"></i> <span class="sidebar-link">Suppliers</span></a>
    </li>
    <li class="<?= $is_active(['categories.php', 'editcategory.php', 'addcategory.php']) ?>">
      <a href="/categories_pages/categories.php"><i class="bi bi-tags"></i> <span class="sidebar-link">Categories</span></a>
    </li>
    <li class="<?= $is_active(['stock_movement.php', 'editstockmovement.php', 'addstockmovement.php']) ?>">
      <a href="/stock_movement_pages/stock_movement.php"><i class="bi bi-arrow-left-right"></i> <span class="sidebar-link">Stock Movement</span></a>
    </li>
    <li class="<?= $is_active(['stock.php', 'editstock.php', 'addstock.php']) ?>">
      <a href="/stock_pages/stock.php"><i class="bi bi-box"></i> <span class="sidebar-link">Stock</span></a>
    </li>
    <li class="<?= $is_active(['po.php', 'editpo.php', 'addpo.php']) ?>">
      <a href="/po_pages/po.php"><i class="bi bi-cart"></i> <span class="sidebar-link">Purchase Order</span></a>
    </li>
    <li class="<?= $is_active(['so.php', 'editso.php', 'addso.php']) ?>">
      <a href="/so_pages/so.php"><i class="bi bi-receipt"></i> <span class="sidebar-link">Sales Order</span></a>
    </li>
    <li>
      <a href="/logout.php" id="logout-btn">
        <i class="bi bi-box-arrow-right"></i> <span class="sidebar-link">Logout</span>
      </a>
    </li>
  </ul>
</div>