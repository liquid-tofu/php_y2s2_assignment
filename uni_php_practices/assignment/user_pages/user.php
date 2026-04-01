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

    <form action="" method="post">
      <div id="search-container">

        <section id="search-type">
          <label for="type">Search by</label>
          <div class="div-btn">
            <select name="type" id="type">
              <option value="id" <?= ($_POST['type'] ?? '') == 'id' ? 'selected' : '' ?>>ID</option>
              <option value="euser" <?= ($_POST['type'] ?? '') == 'euser' ? 'selected' : '' ?>>Username & Email</option>
            </select>
          </div>
          <label for="role">Role</label>
          <div class="div-btn">
            <select name="role" id="role">
              <option value="none"<?= ($_POST['role'] ?? '') == 'none' ? 'selected' : '' ?>>None</option>
              <option value="admin"<?= ($_POST['role'] ?? '') == 'admin' ? 'selected' : '' ?>>Admin</option>
              <option value="manager"<?= ($_POST['role'] ?? '') == 'manager' ? 'selected' : '' ?>>Manager</option>
              <option value="staff"<?= ($_POST['role'] ?? '') == 'staff' ? 'selected' : '' ?>>Staff</option>
              <option value="viewer"<?= ($_POST['role'] ?? '') == 'viewer' ? 'selected' : '' ?>>Viewer</option>
            </select>
          </div>
        </section>

        <section id="search-date">
          <p>from</p>
          <div class="div-btn">
            <input type="date" name="from" value="<?= $_POST['from'] ?? '' ?>">
          </div>
          <p>to</p>
          <div class="div-btn">
            <input type="date" name="to" value="<?= $_POST['to'] ?? '' ?>">
          </div>
        </section>

        <section id="search">
          <input type="text" name="search" id="search-bar" placeholder="Search for..." value="<?= htmlspecialchars($_POST['search'] ?? '') ?>" autocomplete="off">
          <button type="submit" name="submit" id="submit">
            <svg width="34" height="34" viewBox="0 0 34 34" fill="none"><path d="M16 24c4.4183.0 8-3.5817 8-8 0-4.4183-3.5817-8-8-8-4.4183.0-8 3.5817-8 8 0 4.4183 3.5817 8 8 8z" stroke="#e2e2e2ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M26.0001 26.0004l-4.35-4.35" stroke="#e2e2e2ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
          </button>
          <button type="button" name="clear" id="clear">Clear</button>
        </section>
    
      </div>
    </form>

    <table id="content-table">
      <thead>
        <tr>
          <?php
            $heads = ["#", "ID", "Username", "Email", "Role", "Created Time"];
            $arrow = '
            <button type="button" class="arrow-container">
              <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none">
              <path d="M0 5H3L3 16H5L5 5L8 5V4L4 0L0 4V5Z" fill="#fff"/>
              <path d="M8 11L11 11L11 0H13L13 11H16V12L12 16L8 12V11Z" fill="#fff"/>
              </svg>
            </button>';

            foreach(range(0, count($heads) - 1) as $h) {
              echo "<th><div class='head'>" . $heads[$h] . (($h !== 0) ? $arrow : "") . "</div></th>";
            }
          ?>
        </tr>
      </thead>

      <?php
        require('../db.php');
        $result = $conn->query("SELECT * FROM users");

        $i = 0;
        while($row = $result->fetch_assoc()){
          unset($row['password']);
          echo '<tr class="data-row">';
          echo "<td>" . $i + 1 . "</td>";
          foreach($row as $key => $value){
            echo "<td>" . $value . "</td>";
          }
          echo "</tr>";
          $i++;
        }
      ?>
    </table>
  </div>

  <script>
    let searchBar = document.getElementById('search-bar');
    let clear = document.getElementById('clear');

    // Load saved value on page load
    const savedSearch = sessionStorage.getItem('searchTerm');
    if (savedSearch) {
      searchBar.value = savedSearch;
    }

    function checkClear() {
      clear.style.display = searchBar.value !== "" ? 'block' : 'none';
    }

    function saveSearch() {
      sessionStorage.setItem('searchTerm', searchBar.value);
      checkClear();
    }

    searchBar.addEventListener('input', function() {
      saveSearch();
    });

    searchBar.addEventListener('input', checkClear);
    searchBar.addEventListener('keyup', checkClear);
    searchBar.addEventListener('change', checkClear);
    checkClear();

    // for clear btn
    clear.addEventListener('click', function() {
      searchBar.value = '';
      clear.style.display = 'none';
      searchBar.focus();
      sessionStorage.removeItem('searchTerm');
    });
  </script>
</body>
</html>
