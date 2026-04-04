<div class="sidebar">
  <div class="logo">
    <img src="/resources/Logo.svg" alt="Logo">
  </div>
  <ul>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
      <a href="/index.php"><i class="bi bi-speedometer2"></i> <span class="sidebar-link">Dashboard</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'user.php' ? 'active' : '' ?>">
      <a href="/user_pages/user.php"><i class="bi bi-people"></i> <span class="sidebar-link">Users</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
      <a href="/products_pages/products.php"><i class="bi bi-box-seam"></i> <span class="sidebar-link">Products</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'suppliers.php' ? 'active' : '' ?>">
      <a href="/suppiers_pages/suppliers.php"><i class="bi bi-truck"></i> <span class="sidebar-link">Suppliers</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
      <a href="/categories_pages/categories.php"><i class="bi bi-tags"></i> <span class="sidebar-link">Categories</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'stock_movement.php' ? 'active' : '' ?>">
      <a href="/stock_movement_pages/stock_movement.php"><i class="bi bi-arrow-left-right"></i> <span class="sidebar-link">Stock Movement</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'stock.php' ? 'active' : '' ?>">
      <a href="/stock_pages/stock.php"><i class="bi bi-box"></i> <span class="sidebar-link">Stock</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'po.php' ? 'active' : '' ?>">
      <a href="/po_pages/po.php"><i class="bi bi-cart"></i> <span class="sidebar-link">Purchase Order</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'so.php' ? 'active' : '' ?>">
      <a href="/so_pages/so.php"><i class="bi bi-receipt"></i> <span class="sidebar-link">Sales Order</span></a>
    </li>
    <li>
      <a id="logout-btn" href="/logout.php" class="logout-btn">
        <i class="bi bi-box-arrow-right"></i> <span class="sidebar-link">Logout</span>
      </a>
    </li>
  </ul>
</div>


