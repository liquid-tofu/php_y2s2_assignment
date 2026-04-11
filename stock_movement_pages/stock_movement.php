<?php
require('config.php');

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
        <a href="addstockmovement.php" class="add-btn">
          <i class="bi bi-plus-circle"></i>Add
        </a>
        <?php
        $export_params = $_GET;
        $export_params['export'] = 'csv';
        $export_url = 'export_stock_movement.php?' . http_build_query($export_params);
        ?>
        <a href="<?= htmlspecialchars($export_url) ?>" class="export-btn">
          <i class="bi bi-download"></i>Export
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

