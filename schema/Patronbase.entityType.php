<?php

return [
  'name' => 'Patronbase',
  'table' => 'civicrm_patronbase',
  'class' => 'CRM_Patronbase_DAO_Patronbase',
  'module' => 'patronbase',
  'primary_key' => ['sale_id'],
  'getInfo' => fn() => [
    'title' => ts('Patronbase Sale'),
    'title_plural' => ts('Patronbase Sales'),
    'description' => ts('Patronbase Sales Records.'),
  ],
  'getIndices' => fn() => [
    'index_date' => [
      'fields' => [
        'date' => TRUE,
      ],
    ],
    'total' => [
      'fields' => [
        'total' => TRUE,
      ],
    ],
    'sale_id' => [
      'fields' => [
        'sale_id' => TRUE,
      ],
    ],
    'email' => [
      'fields' => [
        'email' => TRUE,
      ],
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => ts('Patron ID'),
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
      'description' => ts('primary key'),
    ],
    'patron' => [
      'title' => ts('Patron ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => ts('Patron ID'),
      'add' => '3.3',
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
    'source' => [
      'title' => ts('Source'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Source'),
    ],

    'referrer' => [
      'title' => ts('Referrer'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'status' => [
      'title' => ts('status'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'email' => [
      'title' => ts('email'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'first_name' => [
      'title' => ts('first_name'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'last_name' => [
      'title' => ts('last_name'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'street_address' => [
      'title' => ts('street_address'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'street_address_supplemental' => [
      'title' => ts('street_address_supplemental'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'postal_code' => [
      'title' => ts('postal_code'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'city' => [
      'title' => ts('city'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'country' => [
      'title' => ts('country'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'state' => [
      'title' => ts('state'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'street_address_other' => [
      'title' => ts('street_address_other'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'street_address_supplemental_other' => [
      'title' => ts('street_address_supplemental_other'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'postal_code_other' => [
      'title' => ts('postal_code_other'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'city_other' => [
      'title' => ts('city_other'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'country_other' => [
      'title' => ts('Country (other)'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'state_other' => [
      'title' => ts('State (other)'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
  ],
];
