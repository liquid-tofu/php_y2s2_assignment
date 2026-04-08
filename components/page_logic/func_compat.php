<?php
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

  echo "<select id='per-page-btn' onchange='move_batch(1, this.value)'>";
  foreach ([5, 10, 20, 50, 100] as $opt) {
    $sel = ($per_page == $opt) ? 'selected' : '';
    echo "<option value='$opt' $sel>{$opt} / page</option>";
  }
  echo "</select>";
}

function display($conn, $where, $params, $types, $config) {
  global $sort_by, $sort_order, $batch, $limit;
  $batch = max(1, (int)$batch);
  $limit = max(1, (int)$limit);
  $offset = ($batch - 1) * $limit;

  // make a list of what col from what tbl to select
  $col_list = [];
  $select_list = [];
  foreach ($config['columns'] as $values) {
    $temp = "{$values[1]}";
    $col_list[] = $temp;
    $select_list[] = "{$temp} AS `{$values[1]}`";
  }
  $select = implode(", ", $select_list);

  // sort bs
  $first_col = reset($config['columns']);
  $default_sort = "{$first_col[1]}";
  $sort_by = in_array($sort_by, $col_list) ? $sort_by : $default_sort;
  $sort_order   = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';

  $whereClause  = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
  $order_clause = "ORDER BY $sort_by $sort_order";
  $sql = "SELECT $select 
          FROM {$config['table']} AS m";
  foreach ($config['joins'] as $j) {
    $sql .= " JOIN {$j[0]} AS {$j[1]} 
              ON {$j[2]} = {$j[3]}";
  }

  $sql .= " $whereClause $order_clause LIMIT $limit OFFSET $offset";
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    die("Prepare failed: " . $conn->error);
  }
  if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
  }

  $stmt->execute();
  $result = $stmt->get_result();
  if (!$result) {
    die("Query failed: " . $stmt->error);
  }
  return $result->fetch_all(MYSQLI_ASSOC);
}
?>



