<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit;
}

require('../db.php');
require('config.php');
$limit = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 10;
$batch = isset($_GET['batch']) ? max(1, (int)$_GET['batch']) : 1;
require('../components/page_logic/count_compat.php');
$sort_by = $_GET['sort_by'] ?? '';
$sort_order = $_GET['sort_order'] ?? '';

$col_list = [];
$select_list = [];
foreach ($origin['columns'] as $values) {
  $temp = "{$values[1]}";
  $col_list[] = $temp;
  $select_list[] = "{$temp} AS `{$values[1]}`";
}
$select = implode(", ", $select_list);

$first_col = reset($origin['columns']);
$default_sort = "{$first_col[1]}";
$sort_by = in_array($sort_by, $col_list, true) ? $sort_by : $default_sort;
$sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';

$whereClause = !empty($countWhere) ? "WHERE " . implode(" AND ", $countWhere) : "";
$sql = "SELECT $select FROM {$origin['table']} AS m";
foreach ($origin['joins'] as $j) {
  $sql .= " JOIN {$j[0]} AS {$j[1]} ON {$j[2]} = {$j[3]}";
}
$sql .= " $whereClause ORDER BY $sort_by $sort_order";

$stmt = $conn->prepare($sql);
if (!empty($countParams)) {
  $stmt->bind_param($countTypes, ...$countParams);
}
$stmt->execute();
$result = $stmt->get_result();
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$filename = 'purchase_orders_export_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputcsv($out, array_merge(['#'], array_keys($origin['columns'])));

$i = 0;
foreach ($rows as $row) {
  $i++;
  $line = [$i];
  foreach ($origin['columns'] as $values) {
    $col_key = $values[1];
    $value = $row[$col_key] ?? '';
    if ($values[0] && !in_array($col_key, ['m.username', 'm.email', 'm.cus_email'], true)) {
      $value = ucwords(strtolower((string)$value));
    }
    $line[] = $value;
  }
  fputcsv($out, $line);
}
fclose($out);
exit;
?>
