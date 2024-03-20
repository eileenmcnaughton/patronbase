<?php
namespace Civi\Api4\Action\Ibis;

use Civi\Api4\Action\ImportBaseAction;
use Civi\Api4\Contact;
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
    $path = __DIR__ . '/../../../../ImportFiles/ibis.csv';

    $csv = Reader::createFromPath($path, 'r');
    $csv->setHeaderOffset(0); //set the CSV header offset

    $stmt = Statement::create()
      ->offset(0)
      ->limit(2000)
    ;

    $patronBaseContactID = $this->getIbisContactID();

    $records = $stmt->process($csv);
    $contribution = [];
    $contributions = [];
    $lines = [];
    foreach ($records as $record) {
      $date = date('Ymd', strtotime($record['Date']));
      if ($record['Line type'] === 'Pay') {
        // The last line in each transaction is the payment line.
        $paymentType = $record['Description'] === '1. Cash' ? 'Cash' : 'EFT';
        $contribution['payment_instrument_id:name'] = $paymentType;
        $key = $date . ' - ' . $paymentType;
        $contribution['source'] = $key . ' Ibis import ';
        $contribution['invoice_id'] = $key;
        if (!array_key_exists($key, $contributions)) {
          $contributions[$key] = $contribution;
        }
        elseif (!empty($contribution['line_items'])) {
          foreach ($contribution['line_items'] as $line_item) {
            $contributions[$key]['line_items'][] = $line_item;
          }
        }
        $contribution = [];
      }
      else {
        if (empty($lines)) {
          $contribution = [
            'receive_date' => $date,
            'contact_id' => $patronBaseContactID,
            'line_items' => [],
            'financial_type_id' => $this->getDefaultFinancialTypeID(),
            'contribution_status_id:name' => 'Completed',
          ];
        }
        $details = array_filter([
          $record['PLU'],
          $record['Description'],
          $record['Item type'],
        ]);
        $lineTotal = (float) \CRM_Utils_Rule::cleanMoney($record['Amount inc']);
        if (str_starts_with($lineTotal, '(')) {
          $lineTotal = -substr($lineTotal, 1, -1);
        }
        $quantity = $record['Units'];
        if (str_starts_with($quantity, '(')) {
          $quantity = -substr($quantity, 1, -1);
        }
        $quantity = (float) $quantity;
        $contribution['line_items'][] = [
          'line_item' => [
            [
              'label' => implode(' - ', $details),
              'field_title' => implode('-', $details),
              'unit_price' => $lineTotal / $quantity,
              'qty' => $record['Units'],
              'line_total' => $lineTotal,
              'financial_type_id' => $this->getDefaultFinancialTypeID(),
            ]
          ]
        ];
      }
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
