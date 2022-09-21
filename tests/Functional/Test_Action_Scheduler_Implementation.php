<?php

/**
 * Functional Tests.
 *
 * Tests that the Action Scheduler Driver can be used to manage Events.
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Functional;

use DateTimeImmutable;
use PinkCrab\Queue\Event\Event;
use PinkCrab\Queue\Event\Async_Event;
use PinkCrab\Queue\Event\Delayed_Event;
use PinkCrab\Queue\Event\Recurring_Event;
use PinkCrab\Queue\Tests\Functional\Abstract_Functional_Test;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Driver;

/**
 * @group functional
 * @group action_scheduler
 */
class Test_Action_Scheduler_Implementation extends \WP_UnitTestCase  {

	/** @testdox It should be possible to use a WP filter to control the path to action scheduler bootstrap file. */
	public function test_it_should_be_possible_to_use_a_wp_filter_to_control_the_path_to_action_scheduler_bootstrap_file() {
		\add_filter(
			'pinkcrab_queue_action_scheduler_path',
			function ( $file ) {
				return 'throws exception';
			}
		);

        // Ensure exception thrown.
        $this->expectException( \RuntimeException::class );
        $this->expectExceptionMessage( 'Action Scheduler is not installed.' );

		Action_Scheduler_Driver::get_instance();
	}

    /** @testdox Whenever another instance of a queue is created, the bootstrap file should not be included again. */
    public function test_whenever_another_instance_of_a_queue_is_created_the_bootstrap_file_should_not_be_included_again() {
        // Create an instance of the driver.
        Action_Scheduler_Driver::get_instance();

        // Set the file to include to be an error.
        \add_filter(
			'pinkcrab_queue_action_scheduler_path',
			function ( $file ) {
				return 'throws exception';
			}
		);
        
        // calling again should not throw an exception as the flag is set.
        Action_Scheduler_Driver::get_instance();
        $this->expectNotToPerformAssertions();
    }

}
