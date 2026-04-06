<?php
$col_block = "name";
$col_block_name = "category";
$tbl_block = "categories";
$block_id = "cat_id";

$tbl_main = "products";
$search_for = ["id", "name"];
$joined = true;

$allowed_columns = ['id', 'name', 'desc', 'price', 'cost', 'category', 'created_at'];
$heads = ["#", "ID", "Name", "Description", "Price", "Cost", "Category", "Created Time", "Actions"];
$col_map = [
  'ID'           => 'id',
  'Name'         => 'name',
  'Description'  => 'desc',
  'Price'        => 'price',
  'Cost'         => 'cost',
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
      <h3>User Management</h3>
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
  const blockName = "<?php echo $block_name; ?>";
  const colMap    = <?php echo json_encode($col_map); ?>;
</script>
<script src="/components/js.js"></script>
<?php require('../components/footer.php'); ?>

