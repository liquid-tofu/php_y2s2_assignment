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

    foreach ($heads as $i => $col) {
      if ($i === 0 || $col === 'Actions') {
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
  $rows = display($conn, $tbl_main, $allowed_columns, $countWhere, $countParams, $countTypes);
  if (empty($rows)) {
    echo "<tr><td colspan='7' style='padding: 20px; text-align: center; color: #aaa;'>No users found</td><tr>";
  } else {
    $i = ($batch - 1) * $limit;
    foreach ($rows as $row) {
      $i++;
      echo '<tr class="data-row">';
      echo "<td>$i</td>";

      foreach ($allowed_columns as $col) {
        $value = $row[$col] ?? $row[trim($col, '`')] ?? '';
        if ($col === $col_block) {
          $value = ucwords(strtolower($value));
        }
        echo "<td>" . htmlspecialchars($value) . "</td>";
      }

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
