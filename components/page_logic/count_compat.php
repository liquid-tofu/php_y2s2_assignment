<?php
$search       = $_GET['search'] ?? $_POST['search'] ?? '';
$search_block = $_GET['block']  ?? $_POST['block']  ?? 'none';
$from         = $_GET['from']   ?? $_POST['from']   ?? '';
$to           = $_GET['to']     ?? $_POST['to']     ?? '';

$sort_by    = $_GET['sort_by']    ?? $_POST['sort_by']      ?? '';
$sort_order = $_GET['sort_order'] ?? $_POST['sort_order']   ?? '';

$countWhere  = [];
$countParams = [];
$countTypes  = "";

if ($search != '') {
  $countWhere[]  = "(CAST(m.{$search_for[0]} AS CHAR) LIKE ? OR m.{$search_for[1]} LIKE ?)";
  $countParams[] = "$search%";
  $countParams[] = "%$search%";
  $countTypes   .= "ss";
}
if ($search_block != 'none') {
  $countWhere[]  = $joined ? "s.$col_block = ?" : "m.$col_block = ?";
  $countParams[] = $search_block;
  $countTypes   .= "s";
}
if ($from != '') {
  $countWhere[]  = "m.created_at >= ?";
  $countParams[] = $from;
  $countTypes   .= "s";
}
if ($to != '') {
  $countWhere[]  = "m.created_at <= ?";
  $countParams[] = $to . " 23:59:59";
  $countTypes   .= "s";
}

$countSql = "SELECT COUNT(*) FROM $tbl_main m";
if ($joined) {
  $countSql .= " JOIN $tbl_block AS s ON m.$block_id = s.id";
}
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
?>
