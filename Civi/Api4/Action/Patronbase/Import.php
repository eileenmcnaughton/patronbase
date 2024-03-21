<?php
namespace Civi\Api4\Action\Patronbase;

use Civi\Api4\Action\ImportBaseAction;
use Civi\Api4\EntityFinancialAccount;
use Civi\Api4\Generic\Result;
use League\Csv\Reader;
use League\Csv\Statement;

/**
 *
 * @package Civi\Api4
 */
class Import extends ImportBaseAction {

  /**
   * @inheritDoc
   *
   * @param \Civi\Api4\Generic\Result $result
   *
   * @throws \API_Exception
   */
  public function _run(Result $result) {
    $path = __DIR__ . '/../../../../ImportFiles/patronbase.csv';

    $csv = Reader::createFromPath($path, 'r');
    $csv->setHeaderOffset(0); //set the CSV header offset
    $stmt = Statement::create()
      ->offset(0)
      ->limit(2000)
    ;

    $patronBaseContactID = $this->getPatronBaseContactID();

    $records = $stmt->process($csv);
    $contributions = [];
    $default = $this->getDefaultFinancialTypeID();
    foreach ($records as $record) {
      $date = date('Ymd', strtotime(str_replace('/', '-', $record['Payment Date'])));
      $paymentType = $record['Payment Type'];
      if (in_array($paymentType, ['Mastercard', 'Visa', 'WeChat', 'Amex'], TRUE)) {
        $paymentType = 'Online Card';
      }
      $key = $date . ' - ' . $paymentType;
      $financialTypeID = empty($record['Account Code']) ? $default : $this->getFinancialAccount($record['Account Code'], $record['Description']);
      if (!array_key_exists($key, $contributions)) {
        $contributions[$key] = [
          'receive_date' => $date,
          'payment_instrument_id:name' => $paymentType,
          'contact_id' => $patronBaseContactID,
          'line_item' => [],
          'invoice_id' => $key,
          'financial_type_id' => $financialTypeID,
          'source' => $key . ' Patronbase import ',
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
