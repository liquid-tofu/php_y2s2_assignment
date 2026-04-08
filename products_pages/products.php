<?php
$origin = [
  'page' => 'product',
  'table' => 'products',
  'use_date' => 'created_at',
  'search' => [
    'int' => 'm.id',
    'txt' => 'm.name'
  ],
  'block' => [
    'table' => 'categories',
    'ali' => 't',
    'column' => 'name',
    'namis' => 'category'
  ],
  'columns' => [
    'ID' => [false, 'm.id'],
    'Name' => [true, 'm.name'],
    'Description' => [true, 'm.desc'],
    'Price' => [false, 'm.price'],
    'Cost' => [false, 'm.cost'],
    'Category' => [true, 't.name'],
    'Created At' => [false, 'm.created_at']
  ],
  'joins' => [
    ['categories', 't', 'm.cat_id', 't.id']
  ]
];

$col_map = [];
foreach ($origin['columns'] as $key => $arr) {
  $col_map[$key] = $arr[1];
}

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
      <h3><?= ucwords(strtolower($origin['page'] . " management")) ?></h3>
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
  const colMap = <?= json_encode($col_map) ?>;
</script>
<script src="/components/js.js"></script>
<?php require('../components/footer.php'); ?>

