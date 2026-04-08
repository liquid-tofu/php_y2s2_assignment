<?php
$search       = $_GET['search']   ?? $_POST['search']     ?? '';
$search_block = $_GET['block']    ?? $_POST['block']      ?? 'none';
$from         = $_GET['from']     ?? $_POST['from']       ?? '';
$to           = $_GET['to']       ?? $_POST['to']         ?? '';

$sort_by    = $_GET['sort_by']    ?? $_POST['sort_by']    ?? '';
$sort_order = $_GET['sort_order'] ?? $_POST['sort_order'] ?? '';

$countWhere  = [];
$countParams = [];
$countTypes  = "";

if ($search != '') {
  $countWhere[]  = "({$origin['search']['int']} = ? OR {$origin['search']['txt']} LIKE ?)";
  $countParams[] = $search;
  $countParams[] = "$search%";
  $countTypes   .= "is";
}
if ($search_block != 'none') {
  $countWhere[]  = "{$origin['block']['ali']}.{$origin['block']['column']} = ?";
  $countParams[] = $search_block;
  $countTypes   .= "s";
}
$date = $origin['use_date'] ?? 'created_at';
if ($from != '') {
  $countWhere[]  = "m.{$date} >= ?";
  $countParams[] = $from;
  $countTypes   .= "s";
}
if ($to != '') {
  $countWhere[]  = "m.{$date} <= ?";
  $countParams[] = $to . " 23:59:59";
  $countTypes   .= "s";
}

$countSql = "SELECT COUNT(*) FROM {$origin['table']} AS m";
foreach ($origin['joins'] as $j) {
  $countSql .= " JOIN {$j[0]} AS {$j[1]} 
                 ON {$j[2]} = {$j[3]}";
}

if (!empty($countWhere)) {
  $countSql .= " WHERE " . implode(" AND ", $countWhere);
}
$countStmt = $conn->prepare($countSql);
if (!$countStmt) {
  die("Prepare failed: " . $conn->error);
}
if (!empty($countParams)) {
  $countStmt->bind_param($countTypes, ...$countParams);
}

$countStmt->execute();
$result = $countStmt->get_result();
if (!$result) {
  die($conn->error);
}

$count = $result->fetch_row()[0];
$limit = max(1, (int)$limit);
$end_batch = ceil($count / $limit);
if ($batch > $end_batch && $end_batch > 0) {
  $batch = $end_batch;
}
?>


