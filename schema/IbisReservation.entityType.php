<?php

return [
  'name' => 'IbisReservation',
  'table' => 'civicrm_hac_ibis_reservation',
  'class' => 'CRM_Ibis_DAO_IbisReservation',
  'module' => 'patronbase',
  'getInfo' => fn() => [
    'title' => ts('Ibis Reservation'),
    'title_plural' => ts('Ibis Reservations'),
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
    'booking_id' => [
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
    'entry_date' => [
      'title' => ts('Entry Date'),
      'sql_type' => 'datetime',
      'input_type' => 'Entry Date',
      'required' => TRUE,
      'description' => ts('Entry date'),
    ],
    'total' => [
      'title' => ts('Sale total'),
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
    'extra_info' => [
      'title' => ts('Extra Info'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'extra_info_group' => [
      'title' => ts('Extra Info Group'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'origin' => [
      'title' => ts('Origin'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'origin_group' => [
      'title' => ts('Origin Group'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'option_summary' => [
      'title' => ts('Option Summary'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'grouping' => [
      'title' => ts('grouping'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
 ],
];
