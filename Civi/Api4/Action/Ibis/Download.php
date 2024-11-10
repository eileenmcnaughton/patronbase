<?php
namespace Civi\Api4\Action\Ibis;

use Civi\Api4\Action\ImportBaseAction;
use Civi\Api4\Generic\Result;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

/**
 *
 * @method string getFromDate()
 * @method string getToDate()
 * @method $this setFromDate(string $fromDate)
 * @method $this setToDate(string $toDate)
 * @package Civi\Api4
 */
class Download extends ImportBaseAction {

  private array $container;

  /**
   * @var string
   */
  public string $fromDate = '3 days ago';

  /**
   * @var string
   */
  public string $toDate = 'yesterday';

  public string $file = '';

  private Client $client;

  /**
   * @inheritDoc
   *
   * @param \Civi\Api4\Generic\Result $result
   */
  public function _run(Result $result) {
    \Civi::log('ibis')->info('starting downloads');
    $this->client = $this->getClient();
    $this->login();
    $this->downloadPosReport($this->client);
    $this->downloadSalesReport($this->client);
    $this->downloadReservations($this->client);
    $this->downloadStockValue();
    $this->downloadStockLedger();
    $this->downloadStockOnHand();

    foreach ($this->container as $transaction) {
      /* @var \GuzzleHttp\Psr7\Request $r */
      $request = $transaction['request'];
      $headers = $request->getHeaders();
      $body = (string) $request->getBody();
      $response = $transaction['response'];
    }
  }

  public function fields() {
    return [];
  }

  /**
   * @return false|mixed|string
   */
  public function getPath() {
    return \CRM_Core_Config::singleton()->uploadDir;
  }

  /**
   *
   * @return \Psr\Http\Message\ResponseInterface
   * @throws \CRM_Core_Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function login(): \Psr\Http\Message\ResponseInterface {
    $response = $this->client->post('/home/Login', [
      'multipart' => [
        ['name' => 'ServerCode', 'contents' => IBIS['ServerCode']],
        ['name' => 'UserName', 'contents' => IBIS['UserName']],
        ['name' => 'password', 'contents' => IBIS['password']],
        ['name' => 'buttonAction', 'contents' => 'ibisAuthLogin'],
      ],
    ]);
    if (!str_contains((string) $response->getBody(), 'Home/Index')) {
      throw new \CRM_Core_Exception('login failed');
    }
    return $response;
  }

  /**
   * @param string $fileName
   * @param \Psr\Http\Message\ResponseInterface $response
   *
   * @return string
   */
  public function writeCSV(string $fileName, \Psr\Http\Message\ResponseInterface $response): string {
    $path = $this->getDirectory() . '/' . $fileName;
    $file = fopen($path, 'wb');
    fwrite($file, (string) $response->getBody());
    \Civi::log('ibis')->info('writing file ' . $path);
    return $path;
  }

  /**
   * Get guzzle client.
   *
   * @return \GuzzleHttp\Client
   */
  public function getClient(): Client {
    $this->container = [];
    $history = Middleware::history($this->container);

    $handlerStack = HandlerStack::create();
    // or $handlerStack = HandlerStack::create($mock); if using the Mock handler.
    // Add the history middleware to the handler stack.
    $handlerStack->push($history);
    $client = new Client([
      'base_uri' => 'https://nxserver.ibisres.com',
      'handler' => $handlerStack,
      'headers' => [
        'User-Agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36",
      ],
      'cookies' => TRUE,
      'debug' => $this->debug,
      'allow_redirects' => FALSE,
    ]);
    return $client;
  }

  /**
   * @param \GuzzleHttp\Client $client
   *
   * @return void
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function downloadPosReport(Client $client): void {
    // Pre-posting this report seems to help it get the view to load into the cookies.
    $client->post('reports/RetailAudit', [
      'form_params' => [
        'ViewName' => 'civi',
        'NewViewName' => 'civi',
        'ViewStyleE' => 'GridListView',
        'PageID' => 'reports.RetailAudit',
        'BrowserTabGUID' => 'da5b7b2a-2d41-4871-bb65-7f7009edb192',
        'ClassName' => 'RptRetailAudit',
        'ReportID' => 'da5b7b2a-2d41-4871-bb65-7f7009edb192.list',
        'ShowTime' => 'False',
        'ShowDates' => 'StartAndEnd',
        'daterange' => date('d m Y', strtotime($this->fromDate)) . ' - ' . date('d m Y', strtotime($this->toDate)) . " 10:00 PM",
        'ExportFormat' => 4,
        'DeleteOnSave' => 'false',
      ],
    ]);
    $posResportResponse = $client->post('reports/RetailAudit', [
      'form_params' => [
        'ViewName' => 'civi',
        'NewViewName' => 'civi',
        'ViewStyleE' => 'GridListView',
        'PageID' => 'reports.RetailAudit',
        'BrowserTabGUID' => 'da5b7b2a-2d41-4871-bb65-7f7009edb192',
        'ClassName' => 'RptRetailAudit',
        'ReportID' => 'da5b7b2a-2d41-4871-bb65-7f7009edb192.list',
        'ShowTime' => 'False',
        'ShowDates' => 'StartAndEnd',
        'daterange' => date('d m Y', strtotime($this->fromDate)) . ' - ' . date('d m Y', strtotime($this->toDate)) . " 10:00 PM",
        'ExportFormat' => 4,
        'FormAction' => 'ExportView',
        'DeleteOnSave' => 'false',
      ],
    ]);
    $this->writeCSV('ibis.csv', $posResportResponse);
  }

  /**
   * @param \GuzzleHttp\Client $client
   *
   * @return void
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function downloadSalesReport(): void {
    $salesResponse = $this->client->post('reports/RetailSales', [
      //BrowserTabGUID	"c731b13b-662f-4d28-aad2-bc83a6c2a0df"
      'form_params' => [
        'ViewStyleE' => "2",
        'ShowTime' => "True",
        'ClassName' => "RptRetailSales",
        'ShowDates' => "StartAndEnd",
        'daterange' => date('d m Y', strtotime($this->fromDate)) . ' 12:00 AM - ' . date('d m Y', strtotime($this->toDate)) . ' 11:59 PM',
        'GstInc' => "false",
        'ExportFormat' => "4",
        'FormAction' => "ExportView",
        'ViewName' => "Civi-export",
        'PageID' => "reports.RetailSales",
        'ReportID' => "c731b13b-662f-4d28-aad2-bc83a6c2a0df.list",
        'NewViewName' => "Civi-export",
        'Notes' => "For+exporting+to+CRM",
        'DeleteOnSave' => "false",
      ]
    ]);

    $this->writeCSV('ibis_sales.csv', $salesResponse);
  }

  /**
   * @param \GuzzleHttp\Client $client
   *
   * @return void
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function downloadReservations(Client $client): void {
    $response = $client->post('https://nxserver.ibisres.com/reports/ReservationSales', [
      'form_params' => [
        'PageID' => "reports.ReservationSales",
        'ReportID' => "c8327bb3-207f-47d4-a7b0-db3ec85abb5d.list",
        'ClassName' => "RptReservationSales",
        'ViewStyleE' => "2",
        'ShowTime' => "True",
        'ShowDates' => "StartAndEnd",
        'daterange' => date('d m Y', strtotime($this->fromDate)) . ' 12:00 AM - ' . date('d m Y', strtotime($this->toDate)) . ' 11:59 PM',
        'GstInc' => "false",
        'ExportFormat' => "4",
        'FormAction' => "ExportView",
        'ViewName' => "CIvi-export",
        'NewViewName' => "CIvi-export",
        'Notes' => "For+exporting+to+CRM",
        'DeleteOnSave' => "false",
      ],
    ]);
    $this->writeCSV('ibis_reservations.csv', $response);
  }

  /**
   * @return void
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function downloadStockValue(): void {
    $response = $this->client->post('https://nxserver.ibisres.com/reports/StockHistoric', [
      'form_params' => [
        'PageID' => "reports.StockHistoric",
        'ReportID' => "4e8d1c08-be11-4582-ba16-bdb525ae2504.list",
        'ClassName' => "RptStockHistoric",
        'ViewStyleE' => "2",
        'ShowTime' => "True",
        'ShowDates' => "StartOnly",
        'daterange' => date('d m Y', strtotime($this->toDate)),
        'GstInc' => "false",
        'ExportFormat' => "4",
        'FormAction' => "ExportView",
        'ViewName' => "CIvi-export",
        'NewViewName' => "CIvi-export",
        'Notes' => "For+exporting+to+CRM",
        'DeleteOnSave' => "false",
      ],
    ]);
    $this->writeCSV('ibis_stock_value.csv', $response);
  }

  /**
   * @return void
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function downloadStockOnHand(): void {
    $response = $this->client->post('reports/StockOnHand', [
      'form_params' => [
        'PageID' => "reports.StockOnHand",
        'ReportID' => "52418099-a39a-484b-9f8e-1b1c81833a05.list",
        'ClassName' => "RptStockOnHand",
        'ViewStyleE' => "2",
        'ShowTime' => "True",
        'ShowDates' => "StartAndEnd",
        'daterange' => date('d m Y', strtotime($this->fromDate)) . ' 12:00 AM - ' . date('d m Y', strtotime($this->toDate)) . ' 11:59 PM',
        'GstInc' => "false",
        'ExportFormat' => "4",
        'FormAction' => "ExportView",
        'ViewName' => "CIvi-export",
        'NewViewName' => "CIvi-export",
        'Notes' => "For+exporting+to+CRM",
        'DeleteOnSave' => "false",
      ],
    ]);
    $this->writeCSV('ibis_stock.csv', $response);
  }

  /**
   * @return void
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function downloadStockLedger(): void {
    $response = $this->client->post('reports/StockLedger', [
      'form_params' => [
        'PageID' => "reports.StockLedger",
        'ReportID' => "7c3849a0-13f9-4bce-bc00-ddaaa3ce8c71.list",
        'ClassName' => "RptStockLedger",
        'ViewStyleE' => "2",
        'ShowTime' => "True",
        'ShowDates' => "StartAndEnd",
        'daterange' => date('d m Y', strtotime($this->fromDate)) . ' 12:00 AM - ' . date('d m Y', strtotime($this->toDate)) . ' 11:59 PM',
        'GstInc' => "false",
        'ExportFormat' => "4",
        'FormAction' => "ExportView",
        'ViewName' => "CIvi-export",
        'NewViewName' => "CIvi-export",
        'Notes' => "For+exporting+to+CRM",
        'DeleteOnSave' => "false",
      ],
    ]);
    $this->writeCSV('ibis_stock_ledger.csv', $response);
  }

}
