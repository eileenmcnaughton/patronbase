<?php

return [
  'name' => 'IbisSale',
  'table' => 'civicrm_hac_ibis_sale',
  'class' => 'CRM_Ibis_DAO_IbisSale',
  'module' => 'patronbase',
  'getInfo' => fn() => [
    'title' => ts('Ibis Sale'),
    'title_plural' => ts('Ibis Sale'),
  ],
  'getIndices' => fn() => [],
  'getFields' => fn() => [
    'id' => [
      'title' => ts('Ibis Reservation ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'sale_id' => [
      'title' => ts('Sale ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
    ],
    'date' => [
      'title' => ts('Sale Date'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'required' => TRUE,
      'description' => ts('Sale date'),
    ],
    'total' => [
      'title' => ts('Sale total'),
      'sql_type' => 'decimal(20,2)',
      'input_type' => 'Text',
      'required' => TRUE,
      'default' => 0,
    ],
    'discount' => [
      'title' => ts('Discount'),
      'sql_type' => 'decimal(20,2)',
      'input_type' => 'Text',
      'required' => TRUE,
      'default' => 0,
    ],
    'product' => [
      'title' => ts('Product'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Product'),
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
    'email' => [
      'title' => ts('email'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'quantity' => [
      'title' => ts('Quantity'),
      'sql_type' => 'int',
      'input_type' => 'Number',
      'description' => ts('Quantity'),
    ],
    'item_type' => [
      'title' => ts('Item Type'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'item_description' => [
      'title' => ts('Item Description'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'detail' => [
      'title' => ts('Detail'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'group_one' => [
      'title' => ts('grouping'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'group_two' => [
      'title' => ts('Sub grouping'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'customer' => [
      'title' => ts('Customer'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'extra_info' => [
      'title' => ts('Extra Info'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'event' => [
      'title' => ts('Event'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'origin_group' => [
      'title' => ts('Origin Group'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'phone' => [
      'title' => ts('Phone'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'supplier' => [
      'title' => ts('Supplier'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'country' => [
      'title' => ts('Country'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'gl' => [
      'title' => ts('GL'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
 ],
];
