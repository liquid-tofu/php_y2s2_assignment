<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>User Management</title>
  <link rel="icon" href="/resources/Logo.ico" id="icon">

  <meta name="description" content="Managing your stock's user account.">
  <meta name="keywords" content="user, stock, order, sale">

  <meta property="og:title" content="User Management">
  <meta property="og:description" content="Managing your stock's user account.">
  <meta property="og:image" content="">

  <link rel="stylesheet" href="/styles/content.css">
</head>
<body>
  <div id="content-container">
    <h3>User Management</h3>
    <hr>

    <form action="user.php" method="POST" id="search-form">
      <div id="search-container">

        <section id="search-type">
          <label for="type">Search by</label>
          <div class="div-btn">
            <select name="type" id="type">
              <option value="id" <?= (($type ?? '') == 'id') ? 'selected' : '' ?>>ID</option>
              <option value="euser" <?= (($type ?? '') == 'euser') ? 'selected' : '' ?>>Username & Email</option>
            </select>
          </div>
          <label for="role">Role</label>
          <div class="div-btn">
            <select name="role" id="role">
              <option value="none" <?= (($role ?? '') == 'none') ? 'selected' : '' ?>>None</option>
              <option value="admin" <?= (($role ?? '') == 'admin') ? 'selected' : '' ?>>Admin</option>
              <option value="manager" <?= (($role ?? '') == 'manager') ? 'selected' : '' ?>>Manager</option>
              <option value="staff" <?= (($role ?? '') == 'staff') ? 'selected' : '' ?>>Staff</option>
              <option value="viewer" <?= (($role ?? '') == 'viewer') ? 'selected' : '' ?>>Viewer</option>
            </select>
          </div>
        </section>

        <section id="search-date">
          <p>from</p>
          <div class="div-btn">
            <input type="date" name="from" value="<?= htmlspecialchars($from ?? '') ?>">
          </div>
          <p>to</p>
          <div class="div-btn">
            <input type="date" name="to" value="<?= htmlspecialchars($to ?? '') ?>">
          </div>
        </section>

        <section id="search">
          <input type="text" name="search" id="search-bar" placeholder="Search for..." value="<?= htmlspecialchars($search ?? '') ?>" autocomplete="off" onkeypress="handleEnter(event)">
          <button type="submit" name="submit" id="submit">
            <svg width="34" height="34" viewBox="0 0 34 34" fill="none"><path d="M16 24c4.4183.0 8-3.5817 8-8 0-4.4183-3.5817-8-8-8-4.4183.0-8 3.5817-8 8 0 4.4183 3.5817 8 8 8z" stroke="#e2e2e2ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M26.0001 26.0004l-4.35-4.35" stroke="#e2e2e2ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
          </button>
          <button type="button" name="clear" id="clear">Clear</button>
        </section>
    
      </div>
    </form>

    <?php
    require('../db.php');

    $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $limit = $per_page;
    $batch = isset($_GET['batch']) ? (int)$_GET['batch'] : 1;

    $type = $_GET['type'] ?? $_POST['type'] ?? 'id';
    $search = $_GET['search'] ?? $_POST['search'] ?? '';
    $role = $_GET['role'] ?? $_POST['role'] ?? 'none';
    $from = $_GET['from'] ?? $_POST['from'] ?? '';
    $to = $_GET['to'] ?? $_POST['to'] ?? '';

    $sort_by = $_GET['sort_by'] ?? $_POST['sort_by'] ?? '';
    $sort_order = $_GET['sort_order'] ?? $_POST['sort_order'] ?? '';
    
    $countWhere = [];
    $countParams = [];
    $countTypes = "";

    if ($search != '') {
      if ($type == 'id') {
        $countWhere[] = "id = ?";
        $countParams[] = $search;
        $countTypes .= "i";
      } elseif ($type == 'euser') {
        $countWhere[] = "(username LIKE ? OR email LIKE ?)";
        $countParams[] = "%$search%";
        $countParams[] = "%$search%";
        $countTypes .= "ss";
      }
    }

    if ($role != 'none') {
      $countWhere[] = "role = ?";
      $countParams[] = $role;
      $countTypes .= "s";
    }

    if ($from != '') {
      $countWhere[] = "created_at >= ?";
      $countParams[] = $from;
      $countTypes .= "s";
    }

    if ($to != '') {
      $countWhere[] = "created_at <= ?";
      $countParams[] = $to . " 23:59:59";
      $countTypes .= "s";
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

    if($batch > $end_batch && $end_batch > 0){
      $batch = $end_batch;
    }

    $heads = ["#", "ID", "Username", "Email", "Role", "Created Time", "Actions"];

    function batch_btns($batch, $end_batch){
      $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
      $btn_nums = [];

      $btn_nums[] = 1;
      if($batch - 2 > 1){
        $btn_nums[] = $batch - 2;
      } if($batch - 1 > 1){
        $btn_nums[] = $batch - 1;
      } if($batch != 1 && $batch != $end_batch){
        $btn_nums[] = $batch;
      } if($batch + 1 < $end_batch){
        $btn_nums[] = $batch + 1;
      } if($batch + 2 < $end_batch){
        $btn_nums[] = $batch + 2;
      } if($end_batch > 1){
        $btn_nums[] = $end_batch;
      }
      
      $btn_nums = array_unique($btn_nums);
      sort($btn_nums);
      
      echo "<div id='batch-btn'>";
      for($i = 0; $i < count($btn_nums); $i++){
        $active_class = ($batch == $btn_nums[$i]) ? "batch-active" : "";
        echo "<button class='$active_class' onclick='move_batch({$btn_nums[$i]}, $per_page)'>$btn_nums[$i]</button>";
      }
      echo "</div>";
      
      echo "<div class='div-btn' style='width: 70px;'>";
      echo "<select onchange='move_batch(1, this.value)'>";
      echo "<option value='5' ".($per_page == 5 ? 'selected' : '').">5/page</option>";
      echo "<option value='10' ".($per_page == 10 ? 'selected' : '').">10/page</option>";
      echo "<option value='20' ".($per_page == 20 ? 'selected' : '').">20/page</option>";
      echo "<option value='50' ".($per_page == 50 ? 'selected' : '').">50/page</option>";
      echo "<option value='100' ".($per_page == 100 ? 'selected' : '').">100/page</option>";
      echo "</select>";
      echo "</div>";
    }
 
    function display($conn) {
      global $batch, $limit, $type, $search, $role, $from, $to, $sort_by, $sort_order;
      $offset = ($batch - 1) * $limit;

      $allowed_sort = ['id', 'username', 'email', 'role', 'created_at'];
      $sort_by = in_array($sort_by, $allowed_sort) ? $sort_by : 'id';
      $sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';
      
      $where = [];
      $params = [];
      $types = "";
      
      if ($search != '') {
        if ($type == 'id') {
          $where[] = "id = ?";
          $params[] = $search;
          $types .= "i";
        } elseif ($type == 'euser') {
          $where[] = "(username LIKE ? OR email LIKE ?)";
          $params[] = "%$search%";
          $params[] = "%$search%";
          $types .= "ss";
        }
      }
      
      if ($role != 'none') {
        $where[] = "role = ?";
        $params[] = $role;
        $types .= "s";
      }
      
      if ($from != '') {
        $where[] = "created_at >= ?";
        $params[] = $from;
        $types .= "s";
      }
      
      if ($to != '') {
        $where[] = "created_at <= ?";
        $params[] = $to . " 23:59:59";
        $types .= "s";
      }
      
      $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
  
      $order_clause = "";
      if ($sort_by != '' && in_array($sort_by, ['id', 'username', 'email', 'role', 'created_at'])) {
        $sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';
        $order_clause = "ORDER BY $sort_by $sort_order";
      }
      $sql = "SELECT * FROM users $whereClause $order_clause LIMIT ? OFFSET ?";

      $params[] = $limit;
      $params[] = $offset;
      $types .= "ii";
      
      $stmt = $conn->prepare($sql);
      $stmt->bind_param($types, ...$params);
      $stmt->execute();
      return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    ?>

    <table id="content-table">
      <thead>
        <tr>
          <?php
          $arrow = '
          <button type="button" class="arrow-container %s" onclick="sortColumn(\'%s\')">
            <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none">
            <path class="arrow-up" d="M0 5H3L3 16H5L5 5L8 5V4L4 0L0 4V5Z" fill="%s"/>
            <path class="arrow-down" d="M8 11L11 11L11 0H13L13 11H16V12L12 16L8 12V11Z" fill="%s"/>
            </svg>
          </button>';

          foreach(range(0, count($heads) - 1) as $h) {
            $col = $heads[$h];
            $arrow_class = '';
            $up_color = '#fff';
            $down_color = '#fff';
            
            if ($h !== 0) {
              $db_col = match($col) {
                'ID' => 'id',
                'Username' => 'username',
                'Email' => 'email',
                'Role' => 'role',
                'Created Time' => 'created_at',
                default => ''
              };
              
              if ($db_col === $sort_by) {
                $arrow_class = 'active';
                if ($sort_order === 'ASC') {
                  $up_color = '#fff';
                  $down_color = '#00BFCB';
                } else {
                  $up_color = '#00BFCB';
                  $down_color = '#fff';
                }
              }
              
              echo "<th><div class='head'>" . $col . sprintf($arrow, $arrow_class, $col, $up_color, $down_color) . "</div></th>";
            } else {
              echo "<th><div class='head'>" . $col . "</div></th>";
            }
          }
          ?>
        </tr>
      </thead>

      <?php
      $users = display($conn);

      if(empty($users)){
        echo "<tr><td colspan='6' style='padding: 20px;'>No users found</td></tr>";
      } else {
        $i = ($batch - 1) * $limit;
        foreach($users as $row){
          $i++;
          echo '<tr class="data-row">';
          echo "<td>" . $i . "</td>";
          echo "<td>" . htmlspecialchars($row['id']) . "</td>";
          echo "<td>" . htmlspecialchars($row['username']) . "</td>";
          echo "<td>" . htmlspecialchars($row['email']) . "</td>";
          echo "<td>" . strtoupper($row['role']) . "</td>";
          echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
          echo "<td class='action-buttons'>
                  <a href='edituser.php?id=" . $row['id'] . "' class='edit-btn'>Edit</a>
                  <a href='deleteuser.php?id=" . $row['id'] . "' class='delete-btn' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                </td>";
          echo "</tr>";
        }
      }
      ?>

      <?php
      $users = display($conn);
      
      if(empty($users)){
        echo "<tr><td colspan='6' style='padding: 20px;'>No users found</td></tr>";
      } else {
        $i = ($batch - 1) * $limit;
        foreach($users as $row){
          $i++;
          echo '<tr class="data-row">';
          echo "<td>" . $i . "</td>";
          
          foreach($row as $key => $value){
            if($key == "password"){
              continue;
            }
            if($key == "role"){
              echo "<td>" . strtoupper($value) . "</td>";
            } else {
              echo "<td>" . htmlspecialchars($value) . "</td>";
            }
          }
          echo "</tr>";
        }
      }
      ?>
    </table>

    <div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0;">
      <a href="adduser.php" class="add-btn">
        <i class="bi bi-plus-circle"></i> Add New User
      </a>
    </div>

    <div id="pagination-container">
      <?php
      $start = ($batch - 1) * $limit + 1;
      $end = min($batch * $limit, $count);
      echo "<p>$start-$end of $count</p>";
      batch_btns($batch, $end_batch);
      ?>
    </div>
  </div>

  <script>
  let searchBar = document.getElementById('search-bar');
  let clear = document.getElementById('clear');
  let typeSelect = document.getElementById('type');
  let roleSelect = document.getElementById('role');
  let fromDate = document.querySelector('input[name="from"]');
  let toDate = document.querySelector('input[name="to"]');

  searchBar.value = sessionStorage.getItem('searchTerm') || '';
  typeSelect.value = sessionStorage.getItem('searchType') || 'id';
  roleSelect.value = sessionStorage.getItem('roleFilter') || 'none';
  fromDate.value = sessionStorage.getItem('fromDate') || '';
  toDate.value = sessionStorage.getItem('toDate') || '';

  function checkClear() {
    clear.style.display = searchBar.value !== "" ? 'block' : 'none';
  }

  function saveAllFilters() {
    sessionStorage.setItem('searchTerm', searchBar.value);
    sessionStorage.setItem('searchType', typeSelect.value);
    sessionStorage.setItem('roleFilter', roleSelect.value);
    sessionStorage.setItem('fromDate', fromDate.value);
    sessionStorage.setItem('toDate', toDate.value);
    checkClear();
  }

  searchBar.addEventListener('input', saveAllFilters);
  typeSelect.addEventListener('change', saveAllFilters);
  roleSelect.addEventListener('change', saveAllFilters);
  fromDate.addEventListener('change', saveAllFilters);
  toDate.addEventListener('change', saveAllFilters);

  searchBar.addEventListener('input', checkClear);
  searchBar.addEventListener('keyup', checkClear);
  searchBar.addEventListener('change', checkClear);
  checkClear();

  clear.addEventListener('click', function() {
    searchBar.value = '';
    sessionStorage.removeItem('searchTerm');
    clear.style.display = 'none';
    searchBar.focus();
  });

  typeSelect.addEventListener('change', function() {
    document.getElementById('search-form').requestSubmit();
  });

  roleSelect.addEventListener('change', function() {
    document.getElementById('search-form').requestSubmit();
  });

  fromDate.addEventListener('change', function() {
    document.getElementById('search-form').requestSubmit();
  });

  toDate.addEventListener('change', function() {
    document.getElementById('search-form').requestSubmit();
  });

  function handleEnter(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      document.getElementById('search-form').requestSubmit();
    }
  }


  function move_batch(batch, per_page) {
    let url = '?batch=' + batch + '&per_page=' + per_page;
    
    if (searchBar.value) {
      url += '&search=' + encodeURIComponent(searchBar.value);
      url += '&type=' + encodeURIComponent(typeSelect.value);
    }
    if (roleSelect.value != 'none') {
      url += '&role=' + encodeURIComponent(roleSelect.value);
    }
    if (fromDate.value) {
      url += '&from=' + encodeURIComponent(fromDate.value);
    }
    if (toDate.value) {
      url += '&to=' + encodeURIComponent(toDate.value);
    }
    
    window.location.href = url;
  }

  if(performance.navigation.type === 1) {
    let savedSearch = sessionStorage.getItem('searchTerm') || '';
    let savedType = sessionStorage.getItem('searchType') || 'id';
    let savedRole = sessionStorage.getItem('roleFilter') || 'none';
    let savedFrom = sessionStorage.getItem('fromDate') || '';
    let savedTo = sessionStorage.getItem('toDate') || '';
    
    let url = '?batch=1&per_page=10';
    
    if (savedSearch) {
      url += '&search=' + encodeURIComponent(savedSearch);
      url += '&type=' + encodeURIComponent(savedType);
    }
    if (savedRole != 'none') {
      url += '&role=' + encodeURIComponent(savedRole);
    }
    if (savedFrom) {
      url += '&from=' + encodeURIComponent(savedFrom);
    }
    if (savedTo) {
      url += '&to=' + encodeURIComponent(savedTo);
    }
    
    window.location.href = url;
  }

  function sortColumn(displayName) {
    const map = {'ID':'id','Username':'username','Email':'email','Role':'role','Created Time':'created_at'};
    let column = map[displayName];
    let urlParams = new URLSearchParams(window.location.search);
    let currentSort = urlParams.get('sort_by') || '';
    let currentOrder = urlParams.get('sort_order') || '';
    
    let newSort = '', newOrder = '';
    
    if (currentSort !== column) {
      newSort = column;
      newOrder = 'DESC';
    } else if (currentSort === column && currentOrder === 'DESC') {
      newSort = column;
      newOrder = 'ASC';
    }
    
    let url = window.location.pathname + '?';
    if (newSort) url += 'sort_by=' + newSort + '&sort_order=' + newOrder + '&';
    url += 'per_page=' + (urlParams.get('per_page') || 10) + '&batch=' + (urlParams.get('batch') || 1);
    
    if (urlParams.get('search')) url += '&search=' + encodeURIComponent(urlParams.get('search')) + '&type=' + encodeURIComponent(urlParams.get('type') || 'id');
    if (urlParams.get('role') && urlParams.get('role') != 'none') url += '&role=' + encodeURIComponent(urlParams.get('role'));
    if (urlParams.get('from')) url += '&from=' + encodeURIComponent(urlParams.get('from'));
    if (urlParams.get('to')) url += '&to=' + encodeURIComponent(urlParams.get('to'));
    
    window.location.href = url;
  }
  </script>
</body>
</html>
