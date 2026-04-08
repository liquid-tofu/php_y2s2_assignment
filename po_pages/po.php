<?php
$block = [
  "table" => "po",
  "column" => "status",
  "name" => "status",
  "block_tbl" => "m"
];
$join = [
  "joined" => true,
  "join_tbl" => "suppliers",
  "join_on" => "supplier_id",
  "join_ft" => "id",
  "join_col" => "name",
  "join_as" => "supplier"
];
$tbl = [
  "table" => "po",
  "sch_index" => "id",
  "sch_text" => "name",
  "sch_tbl" => "s"
];

$allowed_columns = ['id', 'supplier', 'order_date', 'status', 'total_amount', 'created_at'];
$heads = ["#", "ID", "Supplier", "Order Date", "Status", "Total Amount", "Created Time", "Actions"];
$col_map = [
  'ID'           => 'id',
  'Supplier'     => 'supplier',
  'Order Date'   => 'order_date',
  'Status'       => 'status',
  'Total Amount' => 'total_amount',
  'Created Time' => 'created_at',
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
      <h3>Purchase Order Management</h3>
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
        <a href="adduser.php" class="add-btn">
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

