<?php

require_once 'patronbase.civix.php';

use CRM_Patronbase_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function patronbase_civicrm_config(&$config): void {
  _patronbase_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function patronbase_civicrm_install(): void {
  _patronbase_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function patronbase_civicrm_enable(): void {
  _patronbase_civix_civicrm_enable();
}

function patronbase_civicrm_accountPushAlterMapped($entity, &$data, &$save, &$params) {
  if ($entity === 'invoice') {
    foreach ($data['LineItems']['LineItem'] as &$lineItem) {
      $lineItem['Tracking'] = [
        [
          'Name' => 'Division',
          'Option' => 'HAC',
        ],
      ];
    }
  }
}