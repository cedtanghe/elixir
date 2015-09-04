<?php 

if (!defined("ELIXIR_CONFIG_CACHE")) { header("HTTP/1.0 403 Forbidden"); exit(); }

return [
'21e436c4539c8f40e609a72b3c131615' =>  [
    'unit' => [
        'key-1' => 'value-1',
        /*'key-1-callable' => function()
    {
        return Elixir\Facade\Request::get('pilou');
    },*/
    'key-2' => 'value-2'],
    'test > unit' => ['key-2' => 'new value-2']
],
'0434eda9e9bd96e02f7b9a78d12d758c' => array (
  'key-6' => 'new value-6',
  'key-7' => 'value-7',
),
'0bad58410ddbe63b9c219d13af9bdcf7' => array (
  'key-3' => 'new value-3',
  'key-4' => 
  array (
    'key-4-1' => 'value-4-1',
    'key-4-2' => 'value-4-2',
    'key-4-3' => '{REPLACE}value-4-2',
  ),
  'key-5' => 'value-5',
),
];