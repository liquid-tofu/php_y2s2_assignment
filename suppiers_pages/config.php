<?php
$origin = [
  'page' => 'supplier',
  'table' => 'suppliers',
  'use_date' => null,
  'search' => [
    'int' => 'm.id',
    'txt' => 'm.name'
  ],
  'block' => null,
  'columns' => [
    'ID' => [false, 'm.id'],
    'Name' => [true, 'm.name', '', 'text'],
    'Contact Person' => [true, 'm.contact_person', '', 'text'],
    'Email' => [true, 'm.email', '', 'text'],
    'Phone' => [true, 'm.phone', '', 'text'],
    'Address' => [true, 'm.address', '', 'text']
  ],
  'joins' => []
];

$col_map = [];
foreach ($origin['columns'] as $key => $arr) {
  $col_map[$key] = $arr[1];
}
?>
