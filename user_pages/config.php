<?php
$origin = [
  'page' => 'user',
  'table' => 'users',
  'use_date' => 'created_at',
  'search' => [
    'int' => 'm.id',
    'txt' => 'm.username'
  ],
  'block' => [
    'table' => 'users',
    'ali' => 'm',
    'column' => 'role',
    'namis' => 'role'
  ],
  'columns' => [
    'ID' => [false, 'm.id'],
    'Username' => [true, 'm.username', '', 'text'],
    'Email' => [true, 'm.email', '', 'text'],
    'Role' => [true, 'm.role', '', 'select'],
    'Created At' => [false, 'm.created_at']
  ],
  'joins' => []
];

$col_map = [];
foreach ($origin['columns'] as $key => $arr) {
  $col_map[$key] = $arr[1];
}
?>
