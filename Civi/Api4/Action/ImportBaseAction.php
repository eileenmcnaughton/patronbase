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

namespace Civi\Api4\Action;

use Civi\Api4\Contact;
use Civi\Api4\EntityFinancialAccount;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\AbstractEntity;
use Civi\Api4\Generic\Result;

/**
 *
 * @package Civi\Api4
 */
abstract class ImportBaseAction extends AbstractAction {

  /**
   * @var \Civi\Api4\Generic\Result
   */
  private Result $financialAccounts;

  /**
   * @return mixed|null
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  public function getPatronBaseContactID() {
    $patronBaseContactID = Contact::get(FALSE)
      ->addWhere('organization_name', '=', 'Patronbase')
      ->addWhere('contact_type', '=', 'Organization')
      ->execute()->first()['id'] ?? NULL;

    if (!$patronBaseContactID) {
      $patronBaseContactID = Contact::create(FALSE)
        ->setValues([
          'organization_name' => 'Patronbase',
          'contact_type' => 'Organization',
        ])->execute()->first()['id'];
    }
    return $patronBaseContactID;
  }

  /**
   * @return mixed|null
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  public function getIbisContactID() {
    $contactID = Contact::get(FALSE)
      ->addWhere('organization_name', '=', 'Ibis')
      ->addWhere('contact_type', '=', 'Organization')
      ->execute()->first()['id'] ?? NULL;

    if (!$contactID) {
      $contactID = Contact::create(FALSE)
        ->setValues([
          'organization_name' => 'Ibis',
          'contact_type' => 'Organization',
        ])->execute()->first()['id'];
    }
    return $contactID;
  }

  /**
   * @return \Civi\Api4\Generic\Result
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  public function getFinancialAccounts(): Result {
    if (!isset($this->financialAccounts)) {
      $this->financialAccounts = EntityFinancialAccount::get(FALSE)
        ->addWhere('entity_table', '=', 'civicrm_financial_type')
        ->addSelect('entity_id')
        ->addWhere('account_relationship:name', '=', 'Income Account is')
        ->addSelect('account_relationship:name')
        ->addSelect('financial_account_id.*')
        ->addOrderBy('id', 'DESC')
        ->execute()->indexBy('financial_account_id.accounting_code');
    }
    return $this->financialAccounts;
  }

  /**
   * @return int
   */
  public function getDefaultFinancialTypeID(): int {
    // hard-coded - sorry
    return $this->getFinancialAccounts()['30023-1137']['entity_id'] ?? 3;
  }

}
