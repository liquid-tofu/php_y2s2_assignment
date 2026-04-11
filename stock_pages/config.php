<?php
$origin = [
  'page' => 'stock',
  'table' => 'stock',
  'use_date' => 'quantity',
  'search' => [
    'int' => 'm.id',
    'txt' => 's.name'
  ],
  'block' => [
    'table' => 'categories',
    'ali' => 't',
    'column' => 'name',
    'namis' => 'category',
  ],
  'columns' => [
    'ID' => [false, 'm.id'],
    'Product' => [true, 's.name', '', 'select'],
    'Category' => [true, 't.name', '', 'select'],
    'Quantity' => [false, 'm.quantity', '', 'number']
  ],
  'joins' => [
    ['products', 's', 'm.product_id', 's.id'],
    ['categories', 't', 's.cat_id', 't.id']
  ]
];

$col_map = [];
foreach ($origin['columns'] as $key => $arr) {
  $col_map[$key] = $arr[1];
}
?>

