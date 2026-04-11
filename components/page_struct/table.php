<table id="content-table">
<thead>
  <tr>
    <?php
    $arrow_tpl = '
    <button type="button" class="arrow-container %s" onclick="sortColumn(\'%s\')">
      <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none">
        <path class="arrow-up" d="M0 5H3L3 16H5L5 5L8 5V4L4 0L0 4V5Z" fill="%s"/>
        <path class="arrow-down" d="M8 11L11 11L11 0H13L13 11H16V12L12 16L8 12V11Z" fill="%s"/>
      </svg>
    </button>';

    // make borwser consider palin txt
    echo "<td>" . htmlspecialchars('#') . "</td>";
    foreach (array_keys($origin['columns']) as $i => $col) {
      $db_col   = $col_map[$col] ?? '';
      $cls      = '';
      $up_col   = '#fff';
      $down_col = '#fff';
      if ($db_col === $sort_by) {
        $cls      = 'active';
        $up_col   = ($sort_order === 'ASC')  ? '#00BFCB' : '#fff';
        $down_col = ($sort_order === 'DESC') ? '#00BFCB' : '#fff';
      }
      // fill placeholder
      echo "<th><div class='head'>$col" . sprintf($arrow_tpl, $cls, $col, $up_col, $down_col) . "</div></th>";
    }
    echo "<td>" . htmlspecialchars('Actions') . "</td>";
    ?>
  </tr>
</thead>
<tbody>
  <?php
  $rows = display($conn, $countWhere, $countParams, $countTypes, $origin);
  if (empty($rows)) {
    echo "<tr><td colspan='" . (count($origin['columns'])) + 2 . "' style='padding: 20px; text-align: center; color: #aaa;'>No records found</td><tr>";
  } else {
    $i = ($batch - 1) * $limit;
    foreach ($rows as $row) {
      $i++;
      echo '<tr class="data-row">';
      echo "<td>$i</td>";

      $format_cols = [];
      foreach ($origin['columns'] as $values) {
        $col_key = $values[1];
        $value = $row[$col_key] ?? '';

        if (!in_array(($col_key), ['m.username', 'm.email', 'm.cus_email'])) {
          $value = ucwords(strtolower($value));
        }
        echo "<td>" . htmlspecialchars($value) . "</td>";
      }

      $csrf_token = $_SESSION['csrf_token'];
      $id_col = $origin['search']['int'];
      $page = $_SERVER['REQUEST_URI'];
      $current_url = $_SERVER['REQUEST_URI'];
      // encode url
      $return_q = rawurlencode($current_url);
      $confirm_msg = "Are you sure?\\nThe deleted data cannot be recovered.";
      $filename = basename($_SERVER['PHP_SELF'], '.php');
      $file_name = str_replace('_', '', $filename);
      
      if (str_ends_with($file_name, 'ies')) {
        $file_name = substr($file_name, 0, -3) . 'y';
      } elseif (str_ends_with($file_name, 's')) {
        $file_name = substr($file_name, 0, -1);
      }
      $edit_file = "edit{$file_name}.php";

      echo "<td class='action-buttons'>
        <a href='{$edit_file}?id={$row[$id_col]}&return_url={$return_q}' class='edit-btn'>Edit</a>
        <form method='POST' action='/components/mals/delete.php' style='display: inline;'>
          <input type='hidden' name='id' value='{$row[$id_col]}'>
          <input type='hidden' name='tbl' value='{$origin['table']}'>
          <input type='hidden' name='page' value='{$page}'>
          <input type='hidden' name='return_url' value='{$current_url}'>
          <input type='hidden' name='csrf_token' value='{$csrf_token}'>
          <button type='submit' class='delete-btn' onclick='return confirm(\"{$confirm_msg}\")'>Delete</button>
        </form>
      </td>";
    }
  }
  ?>
</tbody>
</table>
