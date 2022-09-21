<?php

/**
 * Unit Tests.
 *
 * Tests that Events can be managed using WC Action Scheduler Driver.
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Unit\Queue_Driver\Action_Scheduler;

use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Driver;

/**
 * @group unit
 * @group action_scheduler
 */
class Test_Action_Scheduler_Driver extends \WP_UnitTestCase {

	/** @testdox It should be possible to get a default instance of the driver from a single call */
	public function test_it_should_be_possible_to_get_a_default_instance_of_the_driver_from_a_single_call() {
		$driver = Action_Scheduler_Driver::get_instance();
		$this->assertInstanceOf( Action_Scheduler_Driver::class, $driver );

		// The intnernal flag should be set.
		$this->assertTrue( Objects::get_property( $driver, 'included' ) );
	}
}
