<?php
require('../components/header.php');
require('../components/sidebar.php');

$search     = $_GET['search']     ?? $_POST['search']     ?? '';
$product_id = $_GET['product_id'] ?? $_POST['product_id'] ?? '';
$type       = $_GET['type']       ?? $_POST['type']       ?? '';
$from       = $_GET['from']       ?? $_POST['from']       ?? '';
$to         = $_GET['to']         ?? $_POST['to']         ?? '';
$sort_by    = $_GET['sort_by']    ?? $_POST['sort_by']    ?? '';
$sort_order = $_GET['sort_order'] ?? $_POST['sort_order'] ?? '';

$countWhere  = [];
$countParams = [];
$countTypes  = "";

if ($search != '') {
  $countWhere[]  = "p.name LIKE ?";
  $countParams[] = "%$search%";
  $countTypes   .= "s";
}
if ($product_id != '') {
  $countWhere[]  = "sm.product_id = ?";
  $countParams[] = $product_id;
  $countTypes   .= "i";
}
if ($type != '') {
  $countWhere[]  = "sm.type = ?";
  $countParams[] = $type;
  $countTypes   .= "s";
}
if ($from != '') {
  $countWhere[]  = "sm.created_at >= ?";
  $countParams[] = $from;
  $countTypes   .= "s";
}
if ($to != '') {
  $countWhere[]  = "sm.created_at <= ?";
  $countParams[] = $to . " 23:59:59";
  $countTypes   .= "s";
}

$countSql = "SELECT COUNT(*) FROM stock_movement sm JOIN products p ON sm.product_id = p.id";
if (!empty($countWhere)) {
  $countSql .= " WHERE " . implode(" AND ", $countWhere);
}
$countStmt = $conn->prepare($countSql);
if (!empty($countParams)) {
  $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$count = $countStmt->get_result()->fetch_row()[0];

$end_batch = ceil($count / $limit);
if ($batch > $end_batch && $end_batch > 0) {
  $batch = $end_batch;
}

$heads = ["#", "ID", "Product Name", "Type", "Quantity", "Note", "Created Time", "Actions"];

function batch_btns($batch, $end_batch) {
  $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
  $btn_nums = [];
  $btn_nums[] = 1;
  if ($batch - 2 > 1)                      $btn_nums[] = $batch - 2;
  if ($batch - 1 > 1)                      $btn_nums[] = $batch - 1;
  if ($batch != 1 && $batch != $end_batch) $btn_nums[] = $batch;
  if ($batch + 1 < $end_batch)             $btn_nums[] = $batch + 1;
  if ($batch + 2 < $end_batch)             $btn_nums[] = $batch + 2;
  if ($end_batch > 1)                      $btn_nums[] = $end_batch;
  $btn_nums = array_unique($btn_nums);
  sort($btn_nums);

  echo "<div id='batch-btn'>";
  foreach ($btn_nums as $num) {
    $active = ($batch == $num) ? "batch-active" : "";
    echo "<button class='$active' onclick='move_batch($num, $per_page)'>$num</button>";
  }
  echo "</div>";

  echo "<div class='div-btn' style='width: 70px;'>";
  echo "<select onchange='move_batch(1, this.value)'>";
  foreach ([5, 10, 20, 50, 100] as $opt) {
    $sel = ($per_page == $opt) ? 'selected' : '';
    echo "<option value='$opt' $sel>{$opt}/page</option>";
  }
  echo "</select></div>";
}

function display($conn) {
  global $batch, $limit, $search, $product_id, $type, $from, $to, $sort_by, $sort_order;
  $offset = ($batch - 1) * $limit;

  $allowed_sort = ['sm.id', 'p.name', 'sm.type', 'sm.quantity', 'sm.created_at'];
  $sort_by      = in_array($sort_by, $allowed_sort) ? $sort_by : 'sm.id';
  $sort_order   = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';

  $where  = [];
  $params = [];
  $types  = "";

  if ($search != '') {
    $where[]  = "p.name LIKE ?";
    $params[] = "%$search%";
    $types   .= "s";
  }
  if ($product_id != '') {
    $where[]  = "sm.product_id = ?";
    $params[] = $product_id;
    $types   .= "i";
  }
  if ($type != '') {
    $where[]  = "sm.type = ?";
    $params[] = $type;
    $types   .= "s";
  }
  if ($from != '') {
    $where[]  = "sm.created_at >= ?";
    $params[] = $from;
    $types   .= "s";
  }
  if ($to != '') {
    $where[]  = "sm.created_at <= ?";
    $params[] = $to . " 23:59:59";
    $types   .= "s";
  }

  $whereClause  = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
  $order_clause = "ORDER BY $sort_by $sort_order";
  $sql = "SELECT sm.id, sm.product_id, sm.type, sm.quantity, sm.note, sm.created_at, p.name as product_name 
          FROM stock_movement sm 
          JOIN products p ON sm.product_id = p.id 
          $whereClause $order_clause LIMIT $limit OFFSET $offset";

  $stmt = $conn->prepare($sql);
  if (!empty($params)) {
      $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getProducts($conn) {
  $sql = "SELECT id, name FROM products ORDER BY name";
  $result = $conn->query($sql);
  return $result->fetch_all(MYSQLI_ASSOC);
}

?>

<div class="main">
  <div class="topbar">
    <h3>Stock Management System</h3>
    <div class="user">
      <i class="bi bi-person-circle"></i> Administrator
    </div>
  </div>

  <div class="content">
    <div id="content-container">
      <h3>Stock Movement</h3>
      <hr>
      <?php
      if (isset($_SESSION['message'])) {
        echo '<div class="popup-message">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
      }
      ?>

      <form action="stock_movement.php" method="GET" id="search-form">
        <div id="search-container">

          <section id="search-type">
            <label for="product_id">Product</label>
            <div class="div-btn">
              <select name="product_id" id="product_id">
                <option value="">All Products</option>
                <?php
                $products = getProducts($conn);
                foreach ($products as $prod) {
                  $selected = ($product_id == $prod['id']) ? 'selected' : '';
                  echo "<option value='{$prod['id']}' $selected>" . htmlspecialchars($prod['name']) . "</option>";
                }
                ?>
              </select>
            </div>
            <label for="type">Type</label>
            <div class="div-btn">
              <select name="type" id="type">
                <option value="">All Types</option>
                <option value="IN" <?= ($type == 'IN') ? 'selected' : '' ?>>IN</option>
                <option value="OUT" <?= ($type == 'OUT') ? 'selected' : '' ?>>OUT</option>
                <option value="ADJUST" <?= ($type == 'ADJUST') ? 'selected' : '' ?>>ADJUST</option>
              </select>
            </div>
          </section>

          <section id="search-date">
            <p>from</p>
            <div class="div-btn">
              <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
            </div>
            <p>to</p>
            <div class="div-btn">
              <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
            </div>
          </section>

          <section id="search">
            <input type="text" name="search" id="search-bar"
                   placeholder="Search by product name..."
                   value="<?= htmlspecialchars($search) ?>"
                   autocomplete="off"
                   onkeypress="handleEnter(event)">
            <button type="button" id="clear">Clear</button>
            <button type="submit" name="submit" id="submit">
              <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
                <path d="M16 24c4.4183 0 8-3.5817 8-8 0-4.4183-3.5817-8-8-8-4.4183 0-8 3.5817-8 8 0 4.4183 3.5817 8 8 8z"
                      stroke="#e2e2e2ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M26.0001 26.0004l-4.35-4.35"
                      stroke="#e2e2e2ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </section>

        </div>
      </form>

      <table id="content-table">
        <thead>
          <tr>
            <?php
            $col_map = [
              'ID'           => 'sm.id',
              'Product Name' => 'p.name',
              'Type'         => 'sm.type',
              'Quantity'     => 'sm.quantity',
              'Created Time' => 'sm.created_at',
            ];
            $arrow_tpl = '
              <button type="button" class="arrow-container %s" onclick="sortColumn(\'%s\')">
                <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none">
                  <path class="arrow-up"   d="M0 5H3L3 16H5L5 5L8 5V4L4 0L0 4V5Z"            fill="%s"/>
                  <path class="arrow-down" d="M8 11L11 11L11 0H13L13 11H16V12L12 16L8 12V11Z" fill="%s"/>
                </svg>
              </button>';

            foreach ($heads as $h => $col) {
              if ($h === 0 || $col === 'Actions' || $col === 'Note') {
                echo "<th><div class='head'>$col</div></th>";
                continue;
              }
              $db_col   = $col_map[$col] ?? '';
              $cls      = '';
              $up_col   = '#fff';
              $down_col = '#fff';
              if ($db_col === $sort_by) {
                $cls      = 'active';
                $up_col   = ($sort_order === 'ASC')  ? '#00BFCB' : '#fff';
                $down_col = ($sort_order === 'DESC') ? '#00BFCB' : '#fff';
              }
              echo "<th><div class='head'>$col" . sprintf($arrow_tpl, $cls, $col, $up_col, $down_col) . "</div></th>";
            }
            ?>
          </tr>
        </thead>
        <tbody>
          <?php
          $movements = display($conn);
          if (empty($movements)) {
            echo "<tr><td colspan='8' style='padding: 20px; text-align: center; color: #aaa;'>No stock movement records found</td></tr>";
          } else {
            $i = ($batch - 1) * $limit;
            foreach ($movements as $row) {
              $i++;
              $type_class = '';
              if ($row['type'] == 'IN') $type_class = 'type-in';
              if ($row['type'] == 'OUT') $type_class = 'type-out';
              if ($row['type'] == 'ADJUST') $type_class = 'type-adjust';
              
              $note = htmlspecialchars($row['note']);
              $note = strlen($note) > 50 ? substr($note, 0, 50) . '...' : $note;
              
              echo '<tr class="data-row">';
              echo "<td>$i</td>";
              echo "<td>" . htmlspecialchars($row['id']) . "</td>";
              echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
              echo "<td class='$type_class'>" . htmlspecialchars($row['type']) . "</td>";
              echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
              echo "<td>" . ($note ?: '-') . "</td>";
              echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
              echo "<td class='action-buttons'>
                      <a href='editstockmovement.php?id={$row['id']}' class='edit-btn'>Edit</a>
                      <a href='deletestockmovement.php?id={$row['id']}' class='delete-btn'
                         onclick='return confirm(\"Are you sure?\")'>Delete</a>
                      </td>";
              echo "</tr>";
            }
          }
          ?>
        </tbody>
      </table>

      <a href="addstockmovement.php" class="add-btn">
        <i class="bi bi-plus-circle"></i> Add Stock Movement
      </a>

      <div id="pagination-container">
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
const searchBar  = document.getElementById('search-bar');
const clearBtn   = document.getElementById('clear');
const productSelect = document.getElementById('product_id');
const typeSelect = document.getElementById('type');
const fromDate   = document.querySelector('input[name="from"]');
const toDate     = document.querySelector('input[name="to"]');

function checkClear() {
  clearBtn.style.display = searchBar.value !== '' ? 'block' : 'none';
}
searchBar.addEventListener('input', checkClear);
checkClear();

clearBtn.addEventListener('click', () => {
  window.location.href = 'stock_movement.php';
});

[productSelect, typeSelect, fromDate, toDate].forEach(el => {
  if (el) {
    el.addEventListener('change', () => {
      const params = new URLSearchParams();
      if (productSelect.value) params.set('product_id', productSelect.value);
      if (typeSelect.value) params.set('type', typeSelect.value);
      if (fromDate.value) params.set('from', fromDate.value);
      if (toDate.value) params.set('to', toDate.value);
      if (searchBar.value) params.set('search', searchBar.value);
      window.location.href = 'stock_movement.php?' + params.toString();
    });
  }
});

function handleEnter(e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    const params = new URLSearchParams();
    if (productSelect.value) params.set('product_id', productSelect.value);
    if (typeSelect.value) params.set('type', typeSelect.value);
    if (fromDate.value) params.set('from', fromDate.value);
    if (toDate.value) params.set('to', toDate.value);
    if (searchBar.value) params.set('search', searchBar.value);
    window.location.href = 'stock_movement.php?' + params.toString();
  }
}

function move_batch(batch, per_page) {
  const params = new URLSearchParams(window.location.search);
  params.set('batch', batch);
  params.set('per_page', per_page);
  window.location.href = 'stock_movement.php?' + params.toString();
}

function sortColumn(displayName) {
  const map = {
    'ID': 'sm.id', 'Product Name': 'p.name', 'Type': 'sm.type', 'Quantity': 'sm.quantity', 'Created Time': 'sm.created_at'
  };
  const column  = map[displayName];
  const params  = new URLSearchParams(window.location.search);
  const curSort = params.get('sort_by')    || '';
  const curOrd  = params.get('sort_order') || '';

  let newSort = '', newOrder = '';
  if (curSort !== column)     { newSort = column; newOrder = 'DESC'; }
  else if (curOrd === 'DESC') { newSort = column; newOrder = 'ASC'; }

  if (newSort) {
    params.set('sort_by', newSort);
    params.set('sort_order', newOrder);
  } else {
    params.delete('sort_by');
    params.delete('sort_order');
  }
  params.delete('batch');
  params.delete('per_page');
  window.location.href = 'stock_movement.php?' + params.toString();
}

if (performance.navigation.type === 1) {
  const url = new URL(window.location.href);
  if (url.searchParams.has('batch') || url.searchParams.has('per_page') || url.searchParams.has('sort_by') || url.searchParams.has('sort_order')) {
    url.searchParams.delete('batch');
    url.searchParams.delete('per_page');
    url.searchParams.delete('sort_by');
    url.searchParams.delete('sort_order');
    window.location.href = url.toString();
  }
}
</script>

<style>
.type-in {
  color: #28a745;
  font-weight: bold;
}
.type-out {
  color: #dc3545;
  font-weight: bold;
}
.type-adjust {
  color: #ffc107;
  font-weight: bold;
}
</style>

<?php require('../components/footer.php'); ?>