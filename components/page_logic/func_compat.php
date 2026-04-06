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

function display($conn, $where, $params, $types) {
  global $col_block, $col_block_name, $tbl_block, $block_id, $tbl_main, $search_for, $joined, $allowed_columns;
  global $sort_by, $sort_order, $batch, $limit;
  $offset = ($batch - 1) * $limit;

  $sort_by      = in_array($sort_by, $allowed_sort) ? $sort_by : 'id';
  $sort_order   = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';

  $select = implode(", ", $allowed_sort);
  $whereClause  = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
  $order_clause = "ORDER BY $sort_by $sort_order";
  $sql          = "SELECT $select FROM $tbl_name $whereClause $order_clause LIMIT $limit OFFSET $offset";

  $stmt = $conn->prepare($sql);
  if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>



