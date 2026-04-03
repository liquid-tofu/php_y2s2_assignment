<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

require('../db.php');

$per_page   = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$limit      = $per_page;
$batch      = isset($_GET['batch'])    ? (int)$_GET['batch']    : 1;

$search     = $_GET['search']     ?? $_POST['search']     ?? '';
$supplier_id = $_GET['supplier_id'] ?? $_POST['supplier_id'] ?? '';
$status     = $_GET['status']     ?? $_POST['status']     ?? '';
$from_date  = $_GET['from_date']  ?? $_POST['from_date']  ?? '';
$to_date    = $_GET['to_date']    ?? $_POST['to_date']    ?? '';
$sort_by    = $_GET['sort_by']    ?? $_POST['sort_by']    ?? '';
$sort_order = $_GET['sort_order'] ?? $_POST['sort_order'] ?? '';

$countWhere  = [];
$countParams = [];
$countTypes  = "";

if ($search != '') {
  $countWhere[]  = "s.name LIKE ?";
  $countParams[] = "%$search%";
  $countTypes   .= "s";
}
if ($supplier_id != '') {
  $countWhere[]  = "po.supplier_id = ?";
  $countParams[] = $supplier_id;
  $countTypes   .= "i";
}
if ($status != '') {
  $countWhere[]  = "po.status = ?";
  $countParams[] = $status;
  $countTypes   .= "s";
}
if ($from_date != '') {
  $countWhere[]  = "po.order_date >= ?";
  $countParams[] = $from_date;
  $countTypes   .= "s";
}
if ($to_date != '') {
  $countWhere[]  = "po.order_date <= ?";
  $countParams[] = $to_date;
  $countTypes   .= "s";
}

$countSql = "SELECT COUNT(*) FROM po JOIN suppliers s ON po.supplier_id = s.id";
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

$heads = ["#", "PO ID", "Supplier", "Order Date", "Status", "Total Amount", "Created At", "Actions"];

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
  global $batch, $limit, $search, $supplier_id, $status, $from_date, $to_date, $sort_by, $sort_order;
  $offset = ($batch - 1) * $limit;

  $allowed_sort = ['po.id', 's.name', 'po.order_date', 'po.status', 'po.total_amount'];
  $sort_by      = in_array($sort_by, $allowed_sort) ? $sort_by : 'po.id';
  $sort_order   = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';

  $where  = [];
  $params = [];
  $types  = "";

  if ($search != '') {
    $where[]  = "s.name LIKE ?";
    $params[] = "%$search%";
    $types   .= "s";
  }
  if ($supplier_id != '') {
    $where[]  = "po.supplier_id = ?";
    $params[] = $supplier_id;
    $types   .= "i";
  }
  if ($status != '') {
    $where[]  = "po.status = ?";
    $params[] = $status;
    $types   .= "s";
  }
  if ($from_date != '') {
    $where[]  = "po.order_date >= ?";
    $params[] = $from_date;
    $types   .= "s";
  }
  if ($to_date != '') {
    $where[]  = "po.order_date <= ?";
    $params[] = $to_date;
    $types   .= "s";
  }

  $whereClause  = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
  $order_clause = "ORDER BY $sort_by $sort_order";
  $sql = "SELECT po.id, po.supplier_id, po.order_date, po.status, po.total_amount, po.created_at, s.name as supplier_name 
          FROM po 
          JOIN suppliers s ON po.supplier_id = s.id 
          $whereClause $order_clause LIMIT $limit OFFSET $offset";

  $stmt = $conn->prepare($sql);
  if (!empty($params)) {
      $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getSuppliers($conn) {
  $sql = "SELECT id, name FROM suppliers ORDER BY name";
  $result = $conn->query($sql);
  return $result->fetch_all(MYSQLI_ASSOC);
}

function getStatusBadge($status) {
  $badges = [
    'PENDING' => '<span class="status-pending">PENDING</span>',
    'APPROVED' => '<span class="status-approved">APPROVED</span>',
    'REJECTED' => '<span class="status-rejected">REJECTED</span>',
    'CANCELLED' => '<span class="status-cancelled">CANCELLED</span>'
  ];
  return $badges[$status] ?? $status;
}

require('../components/header.php');
?>
<link rel="stylesheet" href="/styles/content.css">
<?php require('../components/sidebar.php'); ?>

<div class="main">
  <div class="topbar">
    <h3>Stock Management System</h3>
    <div class="user">
      <i class="bi bi-person-circle"></i> Administrator
    </div>
  </div>

  <div class="content">
    <div id="content-container">
      <h3>Purchase Orders</h3>
      <hr>
      <?php
      if (isset($_SESSION['message'])) {
        echo '<div class="popup-message">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
      }
      ?>

      <form action="po.php" method="GET" id="search-form">
        <div id="search-container">

          <section id="search-type">
            <label for="supplier_id">Supplier</label>
            <div class="div-btn">
              <select name="supplier_id" id="supplier_id">
                <option value="">All Suppliers</option>
                <?php
                $suppliers = getSuppliers($conn);
                foreach ($suppliers as $sup) {
                  $selected = ($supplier_id == $sup['id']) ? 'selected' : '';
                  echo "<option value='{$sup['id']}' $selected>" . htmlspecialchars($sup['name']) . "</option>";
                }
                ?>
              </select>
            </div>
            <label for="status">Status</label>
            <div class="div-btn">
              <select name="status" id="status">
                <option value="">All Status</option>
                <option value="PENDING" <?= ($status == 'PENDING') ? 'selected' : '' ?>>PENDING</option>
                <option value="APPROVED" <?= ($status == 'APPROVED') ? 'selected' : '' ?>>APPROVED</option>
                <option value="REJECTED" <?= ($status == 'REJECTED') ? 'selected' : '' ?>>REJECTED</option>
                <option value="CANCELLED" <?= ($status == 'CANCELLED') ? 'selected' : '' ?>>CANCELLED</option>
              </select>
            </div>
          </section>

          <section id="search-date">
            <p>from</p>
            <div class="div-btn">
              <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>">
            </div>
            <p>to</p>
            <div class="div-btn">
              <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>">
            </div>
          </section>

          <section id="search">
            <input type="text" name="search" id="search-bar"
                   placeholder="Search by supplier..."
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
              'PO ID'        => 'po.id',
              'Supplier'     => 's.name',
              'Order Date'   => 'po.order_date',
              'Status'       => 'po.status',
              'Total Amount' => 'po.total_amount',
            ];
            $arrow_tpl = '
              <button type="button" class="arrow-container %s" onclick="sortColumn(\'%s\')">
                <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none">
                  <path class="arrow-up"   d="M0 5H3L3 16H5L5 5L8 5V4L4 0L0 4V5Z"            fill="%s"/>
                  <path class="arrow-down" d="M8 11L11 11L11 0H13L13 11H16V12L12 16L8 12V11Z" fill="%s"/>
                </svg>
              </button>';

            foreach ($heads as $h => $col) {
              if ($h === 0 || $col === 'Actions' || $col === 'Created At') {
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
          $orders = display($conn);
          if (empty($orders)) {
            echo "<tr><td colspan='8' style='padding: 20px; text-align: center; color: #aaa;'>No purchase orders found</td></tr>";
          } else {
            $i = ($batch - 1) * $limit;
            foreach ($orders as $row) {
              $i++;
              echo '<tr class="data-row">';
              echo "<td>$i</td>";
              echo "<td>" . htmlspecialchars($row['id']) . "</td>";
              echo "<td>" . htmlspecialchars($row['supplier_name']) . "</td>";
              echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
              echo "<td>" . getStatusBadge($row['status']) . "</td>";
              echo "<td>$" . number_format($row['total_amount'], 2) . "</td>";
              echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
              echo "<td class='action-buttons'>
                      <a href='viewpo.php?id={$row['id']}' class='view-btn'>View</a>
                     </td>";
              echo "</tr>";
            }
          }
          ?>
        </tbody>
      </table>

      <a href="addpo.php" class="add-btn">
        <i class="bi bi-plus-circle"></i> Create Purchase Order
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
const supplierSelect = document.getElementById('supplier_id');
const statusSelect = document.getElementById('status');
const fromDate   = document.querySelector('input[name="from_date"]');
const toDate     = document.querySelector('input[name="to_date"]');

function checkClear() {
  clearBtn.style.display = searchBar.value !== '' ? 'block' : 'none';
}
searchBar.addEventListener('input', checkClear);
checkClear();

clearBtn.addEventListener('click', () => {
  window.location.href = 'po.php';
});

[supplierSelect, statusSelect, fromDate, toDate].forEach(el => {
  if (el) {
    el.addEventListener('change', () => {
      const params = new URLSearchParams();
      if (supplierSelect.value) params.set('supplier_id', supplierSelect.value);
      if (statusSelect.value) params.set('status', statusSelect.value);
      if (fromDate.value) params.set('from_date', fromDate.value);
      if (toDate.value) params.set('to_date', toDate.value);
      if (searchBar.value) params.set('search', searchBar.value);
      window.location.href = 'po.php?' + params.toString();
    });
  }
});

function handleEnter(e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    const params = new URLSearchParams();
    if (supplierSelect.value) params.set('supplier_id', supplierSelect.value);
    if (statusSelect.value) params.set('status', statusSelect.value);
    if (fromDate.value) params.set('from_date', fromDate.value);
    if (toDate.value) params.set('to_date', toDate.value);
    if (searchBar.value) params.set('search', searchBar.value);
    window.location.href = 'po.php?' + params.toString();
  }
}

function move_batch(batch, per_page) {
  const params = new URLSearchParams(window.location.search);
  params.set('batch', batch);
  params.set('per_page', per_page);
  window.location.href = 'po.php?' + params.toString();
}

function sortColumn(displayName) {
  const map = {
    'PO ID': 'po.id', 'Supplier': 's.name', 'Order Date': 'po.order_date', 'Status': 'po.status', 'Total Amount': 'po.total_amount'
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
  window.location.href = 'po.php?' + params.toString();
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
.status-pending {
  background: #ffc107;
  color: #000;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}
.status-approved {
  background: #28a745;
  color: #fff;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}
.status-rejected {
  background: #dc3545;
  color: #fff;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}
.status-cancelled {
  background: #6c757d;
  color: #fff;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}
.view-btn {
  background: #17a2b8;
  color: white;
  padding: 4px 8px;
  text-decoration: none;
  border-radius: 4px;
  font-size: 12px;
}
.view-btn:hover {
  background: #138496;
}
</style>

<?php require('../components/footer.php'); ?>