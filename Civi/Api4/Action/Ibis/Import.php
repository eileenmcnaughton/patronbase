<?php
namespace Civi\Api4\Action\Ibis;

use Civi\Api4\Action\ImportBaseAction;
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
    $path = $this->getDirectory() . '/ibis.csv';
    \Civi::log('ibis')->info('loading file ' . $path);
    $csv = Reader::createFromPath($path, 'r');
    $csv->setHeaderOffset(0); //set the CSV header offset

    $stmt = Statement::create()
      ->offset(0)
      ->limit(200000);

    $patronBaseContactID = $this->getIbisContactID();

    $records = $stmt->process($csv);
    $contribution = [];
    $contributions = [];
    $rows = [];

    foreach ($records as $record) {
      if ($record['Status'] !== 'Posted') {
        // wtf is this - skip
        continue;
      }
      $record['payment_type'] = $record['Line type'] !== 'Pay' ? '' : ($record['Description'] === '1. Cash' ? 'Cash' : 'EFT');
      $lineTotal = \CRM_Utils_Rule::cleanMoney($record['Amount inc']);
      if (str_starts_with($lineTotal, '(')) {
        $lineTotal = -substr($lineTotal, 1, -1);
      }
      if (str_starts_with($lineTotal, '(')) {
        $lineTotal = -substr($lineTotal, 1, -1);
      }
      $record['line_total'] = (float) $lineTotal;

      $quantity = $record['Units'];
      if (str_starts_with($quantity, '(')) {
        $quantity = substr($quantity, 1, -1);
      }
      $record['quantity'] = (float) $quantity;
      $record['date'] = date('Ymd', strtotime($record['Date']));
      if ($record['payment_type'] && !empty($lastRecord['payment_type'])) {
        if ($lastRecord['payment_type'] === $record['payment_type']) {
          // 2 payment rows for the same type, combine
          // Remove the last record
          $lastRecord = array_pop($rows);
          $record['line_total'] += $lastRecord['line_total'];
        }
        if ($lastRecord['payment_type'] === 'EFT' && $record['payment_type'] === 'Cash') {
          // Cash needs to come first cos there could be cash-out involved so we need
          // to start with cash & allocate it all & then allocated EFT as needed.
          $removedRecord = array_pop($rows);
          $rows[] = $record;
          $rows[] = $removedRecord;
          $lastRecord = $record;
          continue;
        }
      }
      $lastRecord = $record;
      $rows[] = $record;
    }
    $lineItems = [];
    $partialLines = [];
    foreach ($rows as $record) {
      $date = $record['date'];
      if ($record['Line type'] === 'Pay') {
        // The last line in each transaction is the payment line.
        $paymentType = $record['Description'] === '1. Cash' ? 'Cash' : 'EFT';
        $contribution['payment_instrument_id:name'] = $paymentType;
        $contribution['contact_id'] = $paymentType === 'Cash' ? $this->getIbisCashContactID() : $this->getIbisContactID();
        $key = $date . ' - ' . $paymentType;
        $contribution['source'] = $key . ' Ibis import ';
        $contribution['invoice_id'] = 'ibis_' . $key;

        if (!array_key_exists($key, $contributions)) {
          $contributions[$key] = $contribution;
        }
        $toAllocate = $record['line_total'];

        foreach (array_merge($lineItems, $partialLines) as $lineItem) {
          if (round($toAllocate, 2) !== 0.0) {
            $diff = round($lineItem['line_total'] + $toAllocate, 2);
            if ($diff <= 0.0) {
              $contributions[$key]['line_items'][]['line_item'][] = $lineItem;
            }
            else {
              if ($diff > 0) {
                // This payment covers part of the line,
                $lineItem['label'] = 'split payment ' . $lineItem['label'];
                $partialLine = $lineItem;
                $partialLine['line_total'] += $record['line_total'];
                $partialLine['unit_price'] = $partialLine['line_total'] / $partialLine['qty'];
                $lineItem['line_total'] -= $partialLine['line_total'];
                $lineItem['unit_price'] = $lineItem['line_total'] / $lineItem['qty'];
                $contributions[$key]['line_items'][]['line_item'][] = $lineItem;
                $partialLines[] = $partialLine;
              }
            }
            $toAllocate += $lineItem['line_total'];
          }
          else {
            // The payment is all allocated - pass over to the next payment.
            $partialLines[] = $lineItem;
          }
        }
        $lineItems = [];
        $contribution = [];
      }
      else {
        $partialLines = [];
        if (empty($contribution)) {
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

        $lineItems[] = [
            'label' => implode(' - ', $details),
            'field_title' => implode('-', $details),
            'unit_price' => $record['line_total'] / $record['quantity'],
            'qty' => $record['quantity'],
            'line_total' => $record['line_total'],
            'financial_type_id' => $this->getDefaultFinancialTypeID(),
        ];
      }
    }
    foreach ($contributions as $contribution) {
      try {
        $order = civicrm_api3('Order', 'create', $contribution);
        \civicrm_api3('Payment', 'create', [
          'contribution_id' => $order['id'],
          'total_amount' => $order['values'][$order['id']]['total_amount'],
          'payment_instrument_id' => \CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'payment_instrument_id', $contribution['payment_instrument_id:name']),
        ]);
      }
      catch (\CRM_Core_Exception $e) {
        // skip, try the next one
        \Civi::log()->error('import failed for ' . $contribution['invoice_id'] . ' '  . $e->getMessage());
      }
    }
  }

  public function fields() {
    return [];
  }

  public function setDirectory(string $directory): self {
    $this->directory = $directory;
    return $this;
  }

}
