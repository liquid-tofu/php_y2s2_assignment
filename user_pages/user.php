<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require('../db.php');

if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
  $_SESSION['user_id'] = $_COOKIE['user_id'];
  $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->bind_param("i", $_SESSION['user_id']);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  if ($user) {
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
  } else {
    setcookie('user_id', '', time() - 3600, "/");
    header("Location: ../login.php");
    exit;
  }
}

if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit;
}

$per_page   = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$limit      = $per_page;
$batch      = isset($_GET['batch'])    ? (int)$_GET['batch']    : 1;

$type       = $_GET['type']       ?? $_POST['type']       ?? 'id';
$search     = $_GET['search']     ?? $_POST['search']     ?? '';
$role       = $_GET['role']       ?? $_POST['role']       ?? 'none';
$from       = $_GET['from']       ?? $_POST['from']       ?? '';
$to         = $_GET['to']         ?? $_POST['to']         ?? '';
$sort_by    = $_GET['sort_by']    ?? $_POST['sort_by']    ?? '';
$sort_order = $_GET['sort_order'] ?? $_POST['sort_order'] ?? '';

$countWhere  = [];
$countParams = [];
$countTypes  = "";

if ($search != '') {
  if ($type == 'id') {
    $countWhere[]  = "id = ?";
    $countParams[] = $search;
    $countTypes   .= "i";
  } elseif ($type == 'euser') {
    $countWhere[]  = "(username LIKE ? OR email LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countTypes   .= "ss";
  }
}
if ($role != 'none') {
  $countWhere[]  = "role = ?";
  $countParams[] = $role;
  $countTypes   .= "s";
}
if ($from != '') {
  $countWhere[]  = "created_at >= ?";
  $countParams[] = $from;
  $countTypes   .= "s";
}
if ($to != '') {
  $countWhere[]  = "created_at <= ?";
  $countParams[] = $to . " 23:59:59";
  $countTypes   .= "s";
}

$countSql = "SELECT COUNT(*) FROM users";
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

$heads = ["#", "ID", "Username", "Email", "Role", "Created Time", "Actions"];

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
  global $batch, $limit, $type, $search, $role, $from, $to, $sort_by, $sort_order;
  $offset = ($batch - 1) * $limit;

  $allowed_sort = ['id', 'username', 'email', 'role', 'created_at'];
  $sort_by      = in_array($sort_by, $allowed_sort) ? $sort_by : 'id';
  $sort_order   = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';

  $where  = [];
  $params = [];
  $types  = "";

  if ($search != '') {
    if ($type == 'id') {
      $where[]  = "id = ?";
      $params[] = $search;
      $types   .= "i";
    } elseif ($type == 'euser') {
      $where[]  = "(username LIKE ? OR email LIKE ?)";
      $params[] = "%$search%";
      $params[] = "%$search%";
      $types   .= "ss";
    }
  }
  if ($role != 'none') {
    $where[]  = "role = ?";
    $params[] = $role;
    $types   .= "s";
  }
  if ($from != '') {
    $where[]  = "created_at >= ?";
    $params[] = $from;
    $types   .= "s";
  }
  if ($to != '') {
    $where[]  = "created_at <= ?";
    $params[] = $to . " 23:59:59";
    $types   .= "s";
  }

  $whereClause  = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
  $order_clause = "ORDER BY $sort_by $sort_order";
  $sql = "SELECT id, username, email, role, created_at FROM users $whereClause $order_clause LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
  $params[]     = $limit;
  $params[]     = $offset;
  $types       .= "ii";

  $stmt = $conn->prepare($sql);
  if (!empty($params)) {
      $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

require('../components/header.php');
?>
<link rel="stylesheet" href="/styles/content.css">
<?php
require('../components/sidebar.php');
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
      <h3>User Management</h3>
      <hr>
      <?php
      if (isset($_SESSION['message'])) {
        echo '<div class="popup-message">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
      }
      ?>

      <form action="user.php" method="GET" id="search-form">
        <div id="search-container">

          <section id="search-type">
            <label for="type">Search by</label>
            <div class="div-btn">
              <select name="type" id="type">
                <option value="id"    <?= ($type == 'id')    ? 'selected' : '' ?>>ID</option>
                <option value="euser" <?= ($type == 'euser') ? 'selected' : '' ?>>Username &amp; Email</option>
              </select>
            </div>
            <label for="role">Role</label>
            <div class="div-btn">
              <select name="role" id="role">
                <option value="none"    <?= ($role == 'none')    ? 'selected' : '' ?>>None</option>
                <option value="admin"   <?= ($role == 'admin')   ? 'selected' : '' ?>>Admin</option>
                <option value="manager" <?= ($role == 'manager') ? 'selected' : '' ?>>Manager</option>
                <option value="staff"   <?= ($role == 'staff')   ? 'selected' : '' ?>>Staff</option>
                <option value="viewer"  <?= ($role == 'viewer')  ? 'selected' : '' ?>>Viewer</option>
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
                   placeholder="Search for..."
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
              'ID'           => 'id',
              'Username'     => 'username',
              'Email'        => 'email',
              'Role'         => 'role',
              'Created Time' => 'created_at',
            ];
            $arrow_tpl = '
              <button type="button" class="arrow-container %s" onclick="sortColumn(\'%s\')">
                <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none">
                  <path class="arrow-up"   d="M0 5H3L3 16H5L5 5L8 5V4L4 0L0 4V5Z"            fill="%s"/>
                  <path class="arrow-down" d="M8 11L11 11L11 0H13L13 11H16V12L12 16L8 12V11Z" fill="%s"/>
                </svg>
              </button>';

            foreach ($heads as $h => $col) {
              if ($h === 0 || $col === 'Actions') {
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
          $users = display($conn);
          if (empty($users)) {
            echo "<tr><td colspan='7' style='padding: 20px; text-align: center; color: #aaa;'>No users found</td><tr>";
          } else {
            $i = ($batch - 1) * $limit;
            foreach ($users as $row) {
              $i++;
              echo '<tr class="data-row">';
              echo "<td>$i</td>";
              echo "<td>" . htmlspecialchars($row['id'])         . "</td>";
              echo "<td>" . htmlspecialchars($row['username'])   . "</td>";
              echo "<td>" . htmlspecialchars($row['email'])      . "</td>";
              echo "<td>" . strtoupper($row['role'])             . "</td>";
              echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
              echo "<td class='action-buttons'>
                      <a href='edituser.php?id={$row['id']}' class='edit-btn'>Edit</a>
                      <a href='deleteuser.php?id={$row['id']}' class='delete-btn'
                         onclick='return confirm(\"Are you sure?\")'>Delete</a>
                    </td>";
              echo "</tr>";
            }
          }
          ?>
        </tbody>
      </table>

      <a href="adduser.php" class="add-btn">
        <i class="bi bi-plus-circle"></i> Add New User
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
const typeSelect = document.getElementById('type');
const roleSelect = document.getElementById('role');
const fromDate   = document.querySelector('input[name="from"]');
const toDate     = document.querySelector('input[name="to"]');

function checkClear() {
  clearBtn.style.display = searchBar.value !== '' ? 'block' : 'none';
}
searchBar.addEventListener('input', checkClear);
checkClear();

clearBtn.addEventListener('click', () => {
  window.location.href = 'user.php';
});

[typeSelect, roleSelect, fromDate, toDate].forEach(el => {
  el.addEventListener('change', () => {
    const params = new URLSearchParams();
    params.set('type', typeSelect.value);
    params.set('role', roleSelect.value);
    if (fromDate.value) params.set('from', fromDate.value);
    if (toDate.value) params.set('to', toDate.value);
    if (searchBar.value) params.set('search', searchBar.value);
    window.location.href = 'user.php?' + params.toString();
  });
});

function handleEnter(e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    const params = new URLSearchParams();
    params.set('type', typeSelect.value);
    params.set('role', roleSelect.value);
    if (fromDate.value) params.set('from', fromDate.value);
    if (toDate.value) params.set('to', toDate.value);
    if (searchBar.value) params.set('search', searchBar.value);
    window.location.href = 'user.php?' + params.toString();
  }
}

function move_batch(batch, per_page) {
  const params = new URLSearchParams(window.location.search);
  params.set('batch', batch);
  params.set('per_page', per_page);
  window.location.href = 'user.php?' + params.toString();
}

function sortColumn(displayName) {
  const map = {
    'ID': 'id', 'Username': 'username',
    'Email': 'email', 'Role': 'role', 'Created Time': 'created_at'
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
  window.location.href = 'user.php?' + params.toString();
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

<?php require('../components/footer.php'); ?>