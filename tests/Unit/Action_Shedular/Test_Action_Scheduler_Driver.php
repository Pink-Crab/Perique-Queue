<?php

/**
 * Unit Tests for the Action Scheduler Driver
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Unit\Module;

use PinkCrab\Queue\Tests\Fixture\Event\Logging_Event;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Driver;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Dispatcher;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Queue_Manager;

/**
 * @group unit
 * @group action_scheduler
 * @group driver
 */
class Test_Action_Scheduler_Driver extends \WP_UnitTestCase {

	/** @testdox It should be possible to dispatch any event type via the Driver and have it passed to the underlying dispatcher */
	public function test_it_should_be_possible_to_dispatch_any_event_type_via_the_driver_and_have_it_passed_to_the_underlying_dispatcher() {

		// Mock the dispatcher to return an int.
		$dispatcher_ret_int = $this->createMock( Action_Scheduler_Dispatcher::class );
		$dispatcher_ret_int->method( 'dispatch' )->willReturn( 12 );

		$driver_ret_int = new Action_Scheduler_Driver(
			$dispatcher_ret_int,
			$this->createMock( Action_Scheduler_Queue_Manager::class )
		);

		$this->assertEquals(
			12,
			$driver_ret_int->dispatch( new Logging_Event( 'test' ) )
		);

		// Mock the dispatcher to return null
		$dispatcher_ret_null = $this->createMock( Action_Scheduler_Dispatcher::class );
		$dispatcher_ret_null->method( 'dispatch' )->willReturn( null );

		$driver_ret_null = new Action_Scheduler_Driver(
			$dispatcher_ret_null,
			$this->createMock( Action_Scheduler_Queue_Manager::class )
		);

		$this->assertNull(
			$driver_ret_null->dispatch( new Logging_Event( 'test' ) )
		);
	}

	/** @testdox It should be possible to cancel the next or all events queued via the Driver and have it processed via the underlying Queue Manager */
	public function test_it_should_be_possible_to_cancel_the_next_or_all_events_queued_via_the_driver_and_have_it_processed_via_the_underlying_queue_manager() {

		$calls = array();
		// Mock the Queue Manager to return an int.
		$queue_manager = $this->createMock( Action_Scheduler_Queue_Manager::class );
		$queue_manager->method( 'cancel' )->willReturnCallback(
			function( $event, $next ) use ( &$calls ) {
				$calls[ $next ? 'next' : 'all' ] = $event;
			}
		);

		$driver = new Action_Scheduler_Driver(
			$this->createMock( Action_Scheduler_Dispatcher::class ),
			$queue_manager
		);

		// Cancel Next
		$next_event = new Logging_Event( 'next' );
		$driver->cancel_next( $next_event );

		$this->assertArrayHasKey( 'next', $calls );
		$this->assertEquals( $next_event, $calls['next'] );

		// Cancel All
		$all_event = new Logging_Event( 'test' );
		$driver->cancel_all( $all_event );

		$this->assertArrayHasKey( 'all', $calls );
		$this->assertEquals( $all_event, $calls['all'] );

	}

	/** @testdox It should be to check if an event has already been added to the queue and have this done by the underlying Queue manager */
    public function test_it_should_be_to_check_if_an_event_has_already_been_added_to_the_queue_and_have_this_done_by_the_underlying_queue_manager() {

        $event_has = new Logging_Event( 'has' );
        $event_does_not_have = new Logging_Event( 'does_not_have' );
        
        // Mock the Queue Manager to return based on is event.
        $queue_manager = $this->createMock( Action_Scheduler_Queue_Manager::class );
        $queue_manager->method( 'exists' )->willReturnCallback(
            function( $event ) use ( $event_has ): bool {
                return $event === $event_has;
            }
        );

        $driver = new Action_Scheduler_Driver(
            $this->createMock( Action_Scheduler_Dispatcher::class ),
            $queue_manager
        );

        $this->assertTrue( $driver->is_scheduled( $event_has ) );
        $this->assertFalse( $driver->is_scheduled( $event_does_not_have ) );
    }

}
