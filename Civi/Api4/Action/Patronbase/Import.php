<?php
namespace Civi\Api4\Action\Patronbase;

use Civi\Api4\Contact;
use Civi\Api4\EntityFinancialAccount;
use Civi\Api4\FinancialAccount;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use League\Csv\Reader;
use League\Csv\Statement;

/**
 *
 * @package Civi\Api4
 */
class Import extends AbstractAction {

  /**
   * @inheritDoc
   *
   * @param \Civi\Api4\Generic\Result $result
   *
   * @throws \API_Exception
   */
  public function _run(Result $result) {
    $path = __DIR__ . '/../../../../patronbase.csv';

    $csv = Reader::createFromPath($path, 'r');
    $csv->setHeaderOffset(0); //set the CSV header offset

    //get 25 records starting from the 11th row
    $stmt = Statement::create()
      ->offset(10)
      ->limit(2000)
    ;

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

    $records = $stmt->process($csv);
    $contributions = [];
    $financialAccounts = EntityFinancialAccount::get(FAlSE)
      ->addWhere('entity_table', '=', 'civicrm_financial_type')
      ->addSelect('entity_id')
      ->addWhere('account_relationship:name', '=', 'Income Account is')
      ->addSelect('account_relationship:name')
      ->addSelect('financial_account_id.*')
      ->addOrderBy('id', 'DESC')
      ->execute()->indexBy('financial_account_id.accounting_code');
    // hard-coded - sorry
    $default = $financialAccounts['30023-1137']['entity_id'];
    foreach ($records as $record) {
      $date = date('Ymd', strtotime(str_replace('/', '-', $record['Payment Date'])));
      $key = $date . ' - ' . $record['Payment Type'];
      $financialTypeID = $financialAccounts[$record['Account Code']]['entity_id'] ?? $default;
      if (!array_key_exists($key, $contributions)) {
        $contributions[$key] = [
          'receive_date' => $date,
          'payment_instrument_id:name' => $record['Payment Type'],
          'contact_id' => $patronBaseContactID,
          'line_item' => [],
          'invoice_id' => $key,
          'financial_type_id' => $financialTypeID,
          'source' => $key . ' Patron base import ',
          'contribution_status_id:name' => 'Completed',
        ];
      }
      $details = array_filter([
        $record['Sale ID'],
        $record['ID'],
        $record['ItemType'],
        $record['Payment Type'],
        $record['FirstName'],
        $record['LastName'],
        $record['Account Code'],
      ]);
      $contributions[$key]['line_items'][] = [
        'line_item' => [[
          'label' => implode(' - ', $details),
          'field_title' => implode('-', $details),
          'qty' => 1,
          'unit_price' => $record['Payment Value'],
          'line_total' => $record['Payment Value'],
          'financial_type_id' => $financialTypeID,
        ]]
      ];
    }
    foreach ($contributions as $contribution) {
      try {
        $order = civicrm_api3('Order', 'create', $contribution);
        \civicrm_api3('Payment', 'create', [
          'contribution_id' => $order['id'],
          'total_amount' => $order['values'][$order['id']]['total_amount'],
          'payment_instrument_id' => 1,
        ]);
      }
      catch (\CRM_Core_Exception $e) {
        // skip, try the next one
        \Civi::log()->error('import failed' . $e->getMessage());
      }
    }
  }

  public function fields() {
    return [];
  }

}
