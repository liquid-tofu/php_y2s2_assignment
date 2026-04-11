<?php
$origin = [
  'page' => 'purchase order',
  'table' => 'po',
  'use_date' => 'order_date',
  'search' => [
    'int' => 'm.id',
    'txt' => 's.name'
  ],
  'block' => [
    'table' => 'suppliers',
    'ali' => 's',
    'column' => 'name',
    'namis' => 'supplier'
  ],
  'columns' => [
    'ID' => [false, 'm.id'],
    'Supplier' => [true, 's.name', '', 'select'],
    'Order Date' => [false, 'm.order_date', '', 'datetime-local'],
    'Status' => [true, 'm.status', '', 'select'],
    'Total Amount' => [false, 'm.total_amount', '', 'number'],
    'Created At' => [false, 'm.created_at']
  ],
  'joins' => [
    ['suppliers', 's', 'm.supplier_id', 's.id']
  ]
];

$col_map = [];
foreach ($origin['columns'] as $key => $arr) {
  $col_map[$key] = $arr[1];
}
?>
