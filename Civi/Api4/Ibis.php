<?php

/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

namespace Civi\Api4;

use Civi\Api4\Action\Ibis\Import;
use Civi\Api4\Generic\AbstractEntity;
use Civi\Api4\Generic\BasicGetFieldsAction;

/**
 *
 * @package Civi\Api4
 */
class Ibis extends AbstractEntity {

  /**
   *
   * @param bool $checkPermissions
   *
   * @return \Civi\Api4\Action\Patronbase\Import
   */
  public static function import(bool $checkPermissions = TRUE): Import {
    return (new Import(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }



  /**
   * @return \Civi\Api4\Generic\BasicGetFieldsAction
   */
  public static function getFields() {
    return new BasicGetFieldsAction(__CLASS__, __FUNCTION__, function() {
      return [];
    });
  }

}
