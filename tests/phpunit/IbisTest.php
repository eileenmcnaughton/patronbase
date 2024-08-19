<?php

use Civi\Api4\Contribution;
use Civi\Api4\Ibis;
use Civi\Test;
use CRM_Patronbase_ExtensionUtil as E;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class IbisTest extends TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  /**
   * Setup used when HeadlessInterface is implemented.
   *
   * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
   *
   * @link https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
   *
   * @return \Civi\Test\CiviEnvBuilder
   *
   * @throws \CRM_Extension_Exception_ParseException
   */
  public function setUpHeadless(): CiviEnvBuilder {
    return Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp():void {
    parent::setUp();
  }

  public function tearDown():void {
    parent::tearDown();
  }

  /**
   * Test import.
   */
  public function testImport():void {
    Ibis::import(FALSE)->setDirectory(__DIR__ . '/data')->execute();
    $contributions = Contribution::get(FALSE)
      ->addSelect('*', 'payment_instrument_id:name')
      ->addWhere('contact_id.organization_name', '=', 'Ibis')
      ->execute();
    $this->assertCount(1, $contributions);
    foreach ($contributions as $contribution) {
      $this->assertEquals(730.16, $contribution['total_amount']);
    }
    $contributions = Contribution::get(FALSE)->addWhere('contact_id.organization_name', '=', 'Ibis (Cash)')
      ->execute();
    $this->assertCount(1, $contributions);
    $this->assertEquals(237.80, $contributions->first()['total_amount']);
  }

}
