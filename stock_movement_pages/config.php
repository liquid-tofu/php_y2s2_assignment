<?php
$origin = [
  'page' => 'stock movement',
  'table' => 'stock_movement',
  'use_date' => 'created_at',
  'search' => [
    'int' => 'm.id',
    'txt' => 'p.name'
  ],
  'block' => [
    'table' => 'categories',
    'ali' => 'c',
    'column' => 'name',
    'namis' => 'category'
  ],
  'columns' => [
    'ID' => [false, 'm.id'],
    'Product' => [true, 'p.name', '', 'select'],
    'Type' => [true, 'm.type', '', 'select'],
    'Category' => [true, 'c.name', '', 'select'],
    'Quantity' => [false, 'm.quantity', '', 'number'],
    'Note' => [true, 'm.note', '', 'text'],
    'Created At' => [false, 'm.created_at']
  ],
  'joins' => [
    ['products', 'p', 'm.product_id', 'p.id'],
    ['categories', 'c', 'p.cat_id', 'c.id']
  ]
];

$col_map = [];
foreach ($origin['columns'] as $key => $arr) {
  $col_map[$key] = $arr[1];
}
?>
