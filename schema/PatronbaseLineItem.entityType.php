<?php

return [
  'name' => 'PatronbaseLineItem',
  'table' => 'civicrm_patronbase_line_item',
  'class' => 'CRM_Patronbase_DAO_PatronbaseLineItem',
  'module' => 'patronbase',
  'getInfo' => fn() => [
    'title' => ts('Patronbase Line Item'),
    'title_plural' => ts('Patronbase Line Items'),
    'description' => ts('Patronbase Line Items.'),
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
    'contact_id' => [
      'title' => ts('Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => ts('FK to Contact ID'),
      'input_attrs' => [
        'label' => ts('Contact'),
      ],
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'patronbase_id' => [
      'title' => ts('Patron base ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'entity_reference' => [
        'entity' => 'Patronbase',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
      'input_attrs' => [
        'label' => ts('Patronbase Sale'),
      ],
    ],
    'sale_id' => [
      'title' => ts('Sale ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Text',
      'required' => TRUE,
      'input_attrs' => [
        'label' => ts('Patronbase Sale'),
      ],
    ],

    'product_id' => [
      'title' => ts('Product ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'entity_reference' => [
        'entity' => 'PatronbaseProduct',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
      'input_attrs' => [
        'label' => ts('Product'),
      ],
    ],
    'product_code' => [
      'title' => ts('Product Code'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Product Code.'),
      'required' => TRUE,
    ],
    'quantity' => [
      'title' => ts('Quantity'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => ts('Quantity'),
    ],
    'type' => [
      'title' => ts('Type'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Type'),
    ],
    'price' => [
      'title' => ts('Price'),
      'sql_type' => 'decimal(20,2)',
      'input_type' => 'Text',
      'required' => TRUE,
      'default' => 0,
    ],
  ],
];
