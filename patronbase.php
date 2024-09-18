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
  \Civi::log('ibis')->info('xero hook called with entity: ' . $entity);
  if ($entity === 'invoice') {
    foreach ($params['LineItems']['LineItem'] as &$lineItem) {
      $accounting = substr($lineItem['AccountCode'], 0, 5);
      $mapping = [
        '30011' => '30011 - Hundertwasser Operations',
        '30023' => '30023 - HAC Retail',
        '30020' => '30020 - HAC Public Engagement',
      ];
      \Civi::log()->info('code is ' . $accounting);
      \Civi::log($mapping[$accounting]);
      $lineItem['Tracking'] = [
        'TrackingCategory' => [
          'Name' => 'Division',
          'Option' => 'HAC',
          "TrackingCategoryID" => "d358c4cd-7eff-4446-a874-226648e87854",
          "TrackingOptionID" => "5e73c2b0-317b-4edb-a9b5-8a57510165a8",
        ],
        // The ~ should be removed...
        'TrackingCategory~' => [
          "Name" => "Cost Centre",
          "Option" =>  $mapping[$accounting],
          "TrackingCategoryID" =>  "503d628a-16df-45cf-99af-54aac9d06e5a",
        ],
      ];
    }
  }
}