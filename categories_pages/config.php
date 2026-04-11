<?php
$origin = [
  'page' => 'category',
  'table' => 'categories',
  'use_date' => 'created_at',
  'search' => [
    'int' => 'm.id',
    'txt' => 'm.name'
  ],
  'block' => null,
  'columns' => [
    'ID' => [false, 'm.id'],
    'Name' => [true, 'm.name', '', 'text'],
    'Description' => [true, 'm.desc', '', 'text'],
    'Created At' => [false, 'm.created_at']
  ],
  'joins' => []
];

$col_map = [];
foreach ($origin['columns'] as $key => $arr) {
  $col_map[$key] = $arr[1];
}
?>
