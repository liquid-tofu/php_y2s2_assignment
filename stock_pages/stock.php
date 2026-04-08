<?php
$origin_src = [
  'page' => 'stock',
  'table' => 'stock',
  'ali' => 'm',
  // `stock` has no timestamp column; use `id` to avoid SQL errors
  // when `from`/`to` query params are present.
  'use_date' => 'id',
  'search' => [
    'int' => ['m', 'id'],
    'txt' => ['s', 'name']
  ],
  'block' => [
    'table' => 'categories',
    'alias' => 't',
    'column' => 'name',
    'namis' => 'category',
  ],
  'columns' => [
    'ID' => [false, 'm', 'id'],
    'Product' => [true, 's', 'name', 'm', 'stock'],
    'Category' => [true, 't', 'name', 's', 'products'],
    'Quantity' => [false, 'm', 'quantity']
  ],
  'joins' => [
    ['products', 's', 'm.product_id', 's.id'],
    ['categories', 't', 's.category_id', 't.id']
  ]
];

$col_map = [
  'ID' => 'm.id',
  'Product' => 's.name',
  'Category' => 't.name',
  'Quantity' => 'm.quantity',
];

require('../components/header.php');
require('../components/sidebar.php');
require('../components/page_logic/func_compat.php');
?>

<div class="main">
  <div class="topbar">
    <h3>Stock Management System</h3>
    <div class="user">
      <i class="bi bi-person-circle"></i>
      <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
    </div>
  </div>

  <div class="content">
    <div id="content-container">
      <h3><?= ucwords(strtolower($origin_src['page'] . " management")) ?></h3>
      <hr>
      <?php
      // notification
      if (isset($_SESSION['message'])) {
        echo '<div class="popup-message">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
      }
      ?>

      <form action="" method="GET" id="search-form">
        <div id="search-container">
          <?php require(__DIR__ . '/../components/page_struct/block_bar.php'); ?>
          <?php require(__DIR__ . '/../components/page_struct/time_bar.php'); ?>
          <?php require(__DIR__ . '/../components/page_struct/search_bar.php'); ?>
        </div>
      </form>

      <?php require(__DIR__ . '/../components/page_struct/table.php'); ?>      

      <div id="pagination-container">
        <a href="addstock.php" class="add-btn">
          <i class="bi bi-plus-circle"></i>Add
        </a>
        <?php
        if ($count > 0) {
          $start = ($batch - 1) * $limit + 1;
          $end   = min($batch * $limit, $count);
          echo "<p>{$start}-{$end} of {$count}</p>";
        } else {
          echo "<p>0 results</p>";
        }
        batch_btns($batch, $end_batch);
        ?>
      </div>
    </div>
  </div>
</div>

<script>
  const colMap    = <?php echo json_encode($col_map); ?>;
</script>
<script src="/components/js.js"></script>
<?php require('../components/footer.php'); ?>

