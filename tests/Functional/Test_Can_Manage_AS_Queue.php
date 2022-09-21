<?php

/**
 * Functional Tests.
 *
 * Tests that Events can be managed using WC Action Scheduler.
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Functional;

use DateTimeImmutable;
use PinkCrab\Queue\Event\Async_Event;
use PinkCrab\Queue\Event\Delayed_Event;
use PinkCrab\Queue\Event\Recurring_Event;
use PinkCrab\Queue\Tests\Functional\Abstract_Functional_Test;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Dispatcher;

/**
 * @group functional
 * @group action_scheduler
 */
class Test_Can_Manage_AS_Queue extends Abstract_Functional_Test {

	/**
	 * @testdox It should be possible to dispatch events and have them added to the action schedular queue.
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_can_cancel_events(): void {
		// Cancel Next
		fwrite( STDOUT, " - Cancel Next \n" );

		// Dispatch twice.
		$event = $this->get_async_event( 'test_cancel' );
		$this->action_scheduler_dispatcher->dispatch( $event );
		$this->action_scheduler_dispatcher->dispatch( $event );

		// Cancel Next
		fwrite( STDOUT, " - Cancel Next \n" );
		$this->queue->cancel_next( $event );

		// Check cancelled.
		$this->assertCount( 1, $this->get_events( 'test_cancel', 'pending' ) );
		$this->assertCount( 1, $this->get_events( 'test_cancel', 'canceled' ) );

		// Cancel All
		fwrite( STDOUT, " - Cancel All \n" );
		$this->queue->cancel_all( $event );

		// Check cancelled.
		$this->assertCount( 0, $this->get_events( 'test_cancel', 'pending' ) );
		$this->assertCount( 2, $this->get_events( 'test_cancel', 'canceled' ) );

	}


	/**
	 * @testdox It should be possible to get all pending events
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_can_get_pending_events(): void {
		// Cancel Next
		fwrite( STDOUT, " - Get next \n" );
		$early_date = $this->get_delayed_event( '2030-02-24 00:00:00' );
		$late_date  = $this->get_delayed_event( '2090-02-24 00:00:00' );

		// Dispatch twice.
		$this->action_scheduler_dispatcher->dispatch( $late_date );
		$this->action_scheduler_dispatcher->dispatch( $early_date );

		$result = $this->queue->find_next( $late_date );
		$this->assertSame(
			$early_date->delayed_until()->format( 'Y-m-d H:i:s' ),
			$result->format( 'Y-m-d H:i:s' )
		);

		// No next
		fwrite( STDOUT, " - No next \n" );

		$result = $this->queue->find_next( $this->get_async_event( 'doesnt_exist' ) );
		$this->assertNull( $result );

		// Get all
		fwrite( STDOUT, " - Get all \n" );

		$past_date = $this->get_delayed_event( '2010-02-24 00:00:00' );
		$this->action_scheduler_dispatcher->dispatch( $past_date );

		$result = $this->queue->find_all( $late_date );
		$this->assertCount( 2, $result );
	}

	/**
	 * Returns an async event.
	 *
	 * @return \PinkCrab\Queue\Event\Async_Event
	 */
	private function get_async_event( string $hook ): Async_Event {
		return new class($hook) extends Async_Event{
			protected $hook = 'async';
			protected $data = array( 'foo' => 'bar' );
			public function __construct( string $hook ) {
				$this->hook = $hook;
			}
		};
	}

		/**
	 * Return a delayed event.
	 *
	 * @param string $delay
	 * @return \PinkCrab\Queue\Event\Delayed_Event
	 */
	private function get_delayed_event( string $delay = '1983-02-24 00:00:00' ): Delayed_Event {
		return new class($delay) extends Delayed_Event
		{
			private $delay;
			protected $hook = 'test_delayed_event';
			protected $group = 'delayed';
			protected $data = array( 'foo' => 'bar' );
			public function __construct( string $delay ) {
				$this->delay = $delay;
			}

			public function delayed_until(): ?DateTimeImmutable {
				return new DateTimeImmutable( $this->delay );
			}
		};

	}

	/**
	 * Gets all events from DB based on the hook and status.
	 *
	 * @param string $hook
	 * @param string $status
	 * @return array
	 */
	private function get_events( string $hook, string $status ): array {
		$table_prefix = $GLOBALS['wpdb']->prefix;
		return $GLOBALS['wpdb']
			->get_results( "SELECT * FROM {$table_prefix}actionscheduler_actions WHERE hook='$hook' AND status='$status';" );
	}

}
