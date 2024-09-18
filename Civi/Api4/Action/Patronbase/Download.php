<?php
namespace Civi\Api4\Action\Patronbase;

use Civi\Api4\Action\ImportBaseAction;
use Civi\Api4\EntityFinancialAccount;
use Civi\Api4\Generic\Result;
use League\Csv\Reader;
use League\Csv\Statement;
use function PHPUnit\Framework\stringEndsWith;

/**
 *
 * @package Civi\Api4
 */
class Download extends ImportBaseAction {

  /**
   * @inheritDoc
   *
   * @param \Civi\Api4\Generic\Result $result
   */
  public function _run(Result $result) {
    $path = $this->getPath();

    // retrieve the emails
    try {
      $store = \CRM_Mailing_MailStore::getStore('mail@yeswhangarei.co.nz');
    }
    catch (\Exception $e) {
      $message = ts('Could not connect to MailStore');
      $message .= ts('Error message: ');
      $message .= '<pre>' . $e->getMessage() . '</pre><p>';
      throw new \CRM_Core_Exception($message);
    }

    // process fifty at a time, CRM-4002
    while ($mails = $store->fetchNext()) {
      foreach ($mails as $key => $mail) {
        try {
          $incomingMail = new \CRM_Utils_Mail_IncomingMail($mail, (string) 'yeswhangarei.co.nz', (string) 'mail+');
        }
        catch (\CRM_Core_Exception $e) {
          \Civi::log('ibis')->warning('ignoring mail ' . $e->getMessage());
          $store->markIgnored($key);
          continue;
        }
        $from = $incomingMail->getFrom();
        $from = str_replace('>', '', $from);
        if (!str_ends_with($from, '@mcnaughty.com')
          && !str_ends_with($from, '@bdo.co.nz')
          && !str_ends_with($from, '@wdc.govt.nz')
          && !str_ends_with($from, 'hundertwasserartcentre.co.nz')
        ) {

          \Civi::log('ibis')->warning('ignoring mail from ' . $from);
          $store->markIgnored($key);
          continue;
        }
        $attachments = $incomingMail->getAttachments();
        if (count($attachments) === 1) {
          $fileName = basename($attachments[0]['fullName']);
          \Civi::log('ibis')->info('storing file ' . $path . $fileName);
          rename($attachments[0]['fullName'], $path . $fileName);
        }
        $store->markProcessed($key);
      }
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

}
