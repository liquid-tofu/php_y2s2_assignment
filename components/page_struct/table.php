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

    echo "<td>" . htmlspecialchars('#') . "</td>";
    foreach (array_keys($origin_src['columns']) as $i => $col) {
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
    echo "<td>" . htmlspecialchars('Actions') . "</td>";
    ?>
  </tr>
</thead>
<tbody>
  <?php
  $rows = display($conn, $countWhere, $countParams, $countTypes, $origin_src);
  if (empty($rows)) {
    echo "<tr><td colspan='" . (count($origin_src['columns']) + 2) . "' style='padding: 20px; text-align: center; color: #aaa;'>No records found</td><tr>";
  } else {
    $i = ($batch - 1) * $limit;
    foreach ($rows as $row) {
      $i++;
      echo '<tr class="data-row">';
      echo "<td>$i</td>";

      $format_cols = [];
      foreach ($origin_src['columns'] as $key => $values) {
        if ($values[0]) {
          $format_cols[] = "{$values[1]}.{$values[2]}";
        }
      }
      foreach ($origin_src['columns'] as $key => $values) {
        $col_key = "{$values[1]}.{$values[2]}";
        $value = $row[$col_key] ?? '';
        if (in_array($col_key, $format_cols)) {
          $value = ucwords(strtolower($value));
        }
        echo "<td>" . htmlspecialchars($value) . "</td>";
      }

      $page = $origin_src['page'] ?? '';
      echo "<td class='action-buttons'>
              <a href='edit{$page}.php?id={$row['id']}' class='edit-btn'>Edit</a>
              <a href='delete{$page}.php?id={$row['id']}' class='delete-btn'
                  onclick='return confirm(\"Are you sure?\")'>Delete</a>
            </td>";
      echo "</tr>";
    }
  }
  ?>
</tbody>
</table>
