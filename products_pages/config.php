<?php
$origin = [
  'page' => 'product',
  'table' => 'products',
  'use_date' => 'created_at',
  'search' => [
    'int' => 'm.id',
    'txt' => 'm.name'
  ],
  'block' => [
    'table' => 'categories',
    'ali' => 't',
    'column' => 'name',
    'namis' => 'category'
  ],
  'columns' => [
    'ID' => [false, 'm.id'],
    'Name' => [true, 'm.name', '', 'text'],
    'Description' => [true, 'm.desc', '', 'text'],
    'Price' => [false, 'm.price', '', 'number'],
    'Cost' => [false, 'm.cost', '', 'number'],
    'Category' => [true, 't.name', '', 'select'],
    'Created At' => [false, 'm.created_at']
  ],
  'joins' => [
    ['categories', 't', 'm.cat_id', 't.id']
  ]
];

$col_map = [];
foreach ($origin['columns'] as $key => $arr) {
  $col_map[$key] = $arr[1];
}
?>
