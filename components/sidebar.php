<div class="sidebar">
  <div class="logo">
    <img src="/resources/Logo.svg" alt="Logo">
  </div>
  <ul>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
      <a href="/index.php"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'user.php' ? 'active' : '' ?>">
      <a href="/user_pages/user.php"><i class="bi bi-people"></i> <span>Users</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
      <a href="/products_pages/products.php"><i class="bi bi-box-seam"></i> <span>Products</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'suppliers.php' ? 'active' : '' ?>">
      <a href="/suppiers_pages/suppliers.php"><i class="bi bi-truck"></i> <span>Suppliers</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
      <a href="/categories_pages/categories.php"><i class="bi bi-tags"></i> <span>Categories</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'stock_movement.php' ? 'active' : '' ?>">
      <a href="/stock_movement_pages/stock_movement.php"><i class="bi bi-arrow-left-right"></i> <span>Stock Movement</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'stock.php' ? 'active' : '' ?>">
      <a href="/stock_pages/stock.php"><i class="bi bi-box"></i> <span>Stock</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'po.php' ? 'active' : '' ?>">
      <a href="/po_pages/po.php"><i class="bi bi-cart"></i> <span>Purchase Order</span></a>
    </li>
    <li class="<?= basename($_SERVER['PHP_SELF']) == 'so.php' ? 'active' : '' ?>">
      <a href="/so_pages/so.php"><i class="bi bi-receipt"></i> <span>Sales Order</span></a>
    </li>
    <li>
      <a href="/logout.php" class="logout-btn">
        <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
      </a>
    </li>
  </ul>
</div>