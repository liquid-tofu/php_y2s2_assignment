<?php
$origin = [
  'page' => 'sale order',
  'table' => 'so',
  'use_date' => 'order_date',
  'search' => [
    'int' => 'm.id',
    'txt' => 'm.cus_name'
  ],
  'block' => [
    'table' => 'so',
    'ali' => 'm',
    'column' => 'status',
    'namis' => 'status'
  ],
  'columns' => [
    'ID' => [false, 'm.id'],
    'Customer Name' => [false, 'm.cus_name', '', 'text'],
    'Customer Email' => [false, 'm.cus_email', '', 'text'],
    'Sale Date' => [false, 'm.order_date', '', 'datetime-local'],
    'Status' => [false, 'm.status', '', 'select'],
    'Total Amount' => [false, 'm.total_amount', '', 'number']
  ],
  'joins' => []
];

$col_map = [];
foreach ($origin['columns'] as $key => $arr) {
  $col_map[$key] = $arr[1];
}
?>
