<?php
namespace Civi\Api4\Action\Ibis;

use Civi\Api4\Action\ImportBaseAction;
use Civi\Api4\Generic\Result;
use League\Csv\Reader;
use League\Csv\Statement;

/**
 *
 * @method $this setFileName(string $fileName)
 * @package Civi\Api4
 */
class Import extends ImportBaseAction {

  /**
   * @var string
   */
  protected string $fileName = 'ibis.csv';

  /**
   * @inheritDoc
   *
   * @param \Civi\Api4\Generic\Result $result
   *
   * @throws \CRM_Core_Exception
   * @throws \League\Csv\Exception
   */
  public function _run(Result $result): void {
    $records = [];
    $lastRecord = ['payment_type' => 'first row'];
    foreach ($this->getRecords() as $record) {
      $record['payment_type'] = $record['Line type'] !== 'Pay' ? '' : ($record['Description'] === '1. Cash' ? 'Cash' : 'EFT');
      if ($record['payment_type'] && $record['Till'] === 'online') {
        $record['payment_type'] = 'Online Card';
      }
      $date = date('Ymd', strtotime($record['Date']));
      $hourMinute = (int) date('Hi', strtotime(str_replace('/', '-', $record['Time'])));
      if ($hourMinute > 2200) {
        $date = date('Ymd', strtotime('+ 1 day', strtotime($date)));
      }
      $lineTotal = \CRM_Utils_Rule::cleanMoney($record['Amount inc']);
      if (str_starts_with($lineTotal, '(')) {
        $lineTotal = -substr($lineTotal, 1, -1);
      }
      if (str_starts_with($lineTotal, '(')) {
        $lineTotal = -substr($lineTotal, 1, -1);
      }
      $record['line_total'] = (float) $lineTotal;
      $record['rounding'] = 0;

      $quantity = $record['Units'];
      if (str_starts_with($quantity, '(')) {
        $quantity = substr($quantity, 1, -1);
      }
      $record['quantity'] = (float) $quantity;
      $record['date'] = $date;
      if ($record['payment_type'] === 'Cash' && $record['line_total'] >= -.1 && $record['line_total'] <= .1) {
        // This is a rounding.
        $record['rounding'] = $record['line_total'];
      }

      if ($lastRecord['payment_type'] === $record['payment_type']) {
        // 2 payment rows for the same type, combine
        // Remove the last record
        $lastRecord = array_pop($records);
        $record['rounding'] += $lastRecord['rounding'];
        $record['line_total'] += $lastRecord['line_total'];
      }
      $records[] = $record;
      $lastRecord = $record;
    }

    $contribution = [];
    $contributions = [];
    $rows = [];

    foreach ($records as $record) {
      if ($record['Status'] !== 'Posted') {
        // wtf is this - skip
        continue;
      }
      if ($record['payment_type'] && !empty($lastRecord['payment_type'])) {
        if ($lastRecord['payment_type'] === $record['payment_type']) {
          // 2 payment rows for the same type, combine
          // Remove the last record
          $lastRecord = array_pop($rows);
          $record['rounding'] += $lastRecord['rounding'];
          $record['line_total'] += $lastRecord['line_total'];
        }
        if ($lastRecord['payment_type'] === 'EFT' && $record['payment_type'] === 'Cash') {
          // Cash needs to come first cos there could be cash-out involved, so we need
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
        $paymentType = $this->getPaymentType($record);
        $contribution['payment_instrument_id:name'] = $paymentType;
        $contribution['receive_date'] = $date;
        $contribution['contact_id'] = $paymentType === 'Cash' ? $this->getIbisCashContactID() : $this->getIbisContactID();
        $key = $date . ' - ' . $paymentType;
        $contribution['source'] = $key . ' Ibis import ';
        $contribution['invoice_id'] = 'ibis_' . $key;
        $contribution['financial_type_id'] = $this->getDefaultFinancialTypeID();
        if (!empty($record['rounding'])) {
          $lineItems[] = [
            'label' => 'Rounding',
            'field_title' => 'Rounding',
            'unit_price' => $record['rounding'],
            'qty' => 1,
            'line_total' => $record['rounding'],
            'financial_type_id' => $this->getRoundingFinancialTypeID(),
          ];
        }

        if (!array_key_exists($key, $contributions)) {
          $contributions[$key] = $contribution;
        }
        $toAllocate = $record['line_total'] - $record['rounding'];

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
                // We have a line where the amount to pay is great than the remaining
                // amount to allocate - so the diff goes to the partial line
                $lineItem['line_total'] -= $diff;
                $partialLine['line_total'] = $diff;

                $partialLine['unit_price'] = $partialLine['line_total'] / $partialLine['qty'];
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
        $contribution += [
          'line_items' => [],
        ];
        $details = array_filter([
          $record['Trans #'] ?? NULL,
          $record['PLU'],
          $record['Description'],
          $record['Item type'],
        ]);
        $financialTypeID = $this->getDefaultFinancialTypeID();
        if ($record['Item type'] === 'Reservation') {
          $financialTypeID =  $this->getFinancialAccount('30011-1117', 'Admissions');
        }
        elseif ($record['Till'] === 'online') {
          if ($record['Item type'] === 'Non stock') {
            $financialTypeID = $this->getFinancialAccount('30023-1192', 'Postage');
          }
          else {
            $financialTypeID = $this->getFinancialAccount('30023-1138', 'Online sales');
          }
        }
        $lineItems[] = [
          'label' => implode(' - ', $details),
          'field_title' => implode('-', $details),
          'unit_price' => $record['line_total'] / $record['quantity'],
          'qty' => $record['quantity'],
          'line_total' => $record['line_total'],
          'financial_type_id' => $financialTypeID,
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
        \Civi::log()->error('import failed for ' . $contribution['invoice_id'] . ' ' . $e->getMessage());
      }
    }
  }

  public function fields(): array {
    return [];
  }

  public function setDirectory(string $directory): self {
    $this->directory = $directory;
    return $this;
  }

  /**
   * @return string
   */
  public function getPath(): string {
    $path = $this->getDirectory() . '/' . $this->fileName;
    \Civi::log('ibis')->info('loading file ' . $path);
    return $path;
  }

  /**
   * @return \League\Csv\Reader
   * @throws \League\Csv\Exception
   */
  public function getCsv(): Reader {
    $path = $this->getPath();
    $csv = Reader::createFromPath($path, 'r');
    $csv->setHeaderOffset(0);
    $csv->addStreamFilter('convert.iconv.ASCII/UTF-8');
    return $csv;
  }

  /**
   * @return \League\Csv\TabularDataReader
   * @throws \League\Csv\Exception
   */
  public function getRecords(): \League\Csv\TabularDataReader {
    $csv = $this->getCsv();

    $stmt = Statement::create()
      ->offset(0)
      ->limit(200000);

    $records = $stmt->process($csv);
    return $records;
  }

  /**
   * @param string $a
   *
   * @return string|int
   */
  public function getCurrencyAmount(string $a) {
    return str_replace(['$', ','], '', $a) ?: 0;
  }

  /**
   * @param mixed $record
   *
   * @return string
   */
  public function getPaymentType(mixed $record): string {
    if ($record['Till'] === 'online') {
      return 'Credit Card';
    }
    if ($record['Description'] === '5. Other') {
      return 'Adjustment';
    }
    return $record['Description'] === '1. Cash' ? 'Cash' : 'EFT';

  }
}
