<?php

return [
  'name' => 'PatronbaseProduct',
  'table' => 'civicrm_patronbase_product',
  'class' => 'CRM_Patronbase_DAO_PatronbaseProduct',
  'module' => 'patronbase',
  'getInfo' => fn() => [
    'title' => ts('Patronbase Product'),
    'title_plural' => ts('Patronbase Products'),
    'description' => ts('Patronbase Products.'),
  ],
  'getIndices' => fn() => [],
  'getFields' => fn() => [
    'id' => [
      'title' => ts('Patron base line item ID ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'product_code' => [
      'title' => ts('Product Code'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Product Code.'),
      'required' => TRUE,
    ],
    'price' => [
      'title' => ts('Price'),
      'sql_type' => 'decimal(20,2)',
      'input_type' => 'Text',
      'required' => TRUE,
      'default' => 0,
    ],
    'stock_value' => [
      'title' => ts('Stock Value'),
      'sql_type' => 'decimal(20,2)',
      'input_type' => 'Text',
      'required' => TRUE,
      'default' => 0,
    ],
    'description' => [
      'title' => ts('Description'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Description'),
    ],
    'is_local' => [
      'title' => ts('Is local'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'default' => FALSE,
    ],
    'person_type' => [
      'title' => ts('Person Type'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Person Type'),
    ],
    'type' => [
      'title' => ts('Type'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Type'),
    ],
    'admit_to' => [
      'title' => ts('Admit To'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Admit To'),
    ],
    'current_units' => [
      'title' => ts('Current units'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => ts('Current units'),
    ],
    'supplier' => [
      'title' => ts('Supplier'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Supplier'),
    ],
    'supplier_code' => [
      'title' => ts('Supplier Code'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Supplier Code'),
    ],
    'group_one' => [
      'title' => ts('Classification 1'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'group_two' => [
      'title' => ts('Classification 2'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
  ],
];
