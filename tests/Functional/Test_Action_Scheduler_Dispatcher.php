<?php

/**
 * Functional Tests.
 *
 * Tests that Events can be dispatched and cancelled using WC Action Scheduler.
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
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Dispatcher;

/**
 * @group functional
 * @group action_scheduler
 */
class Test_Action_Scheduler_Dispatcher extends Abstract_Functional_Test {


	/**
	 * @testdox It should be possible to dispatch events and have them added to the action schedular queue.
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_can_dispatch_events(): void {
		// Dispatch async event
		fwrite( STDOUT, " - Dispatch async event \n" );
		$async       = $this->get_async_event();
		$async_id    = $this->action_scheduler_dispatcher->dispatch( $async );
		$async_queue = $GLOBALS['wpdb']->get_row( "SELECT * FROM {$GLOBALS['wpdb']->prefix}actionscheduler_actions WHERE action_id = $async_id" );
		$async_group = $GLOBALS['wpdb']->get_row( "SELECT * FROM {$GLOBALS['wpdb']->prefix}actionscheduler_groups WHERE group_id = {$async_queue->group_id}" );

		// Check was dispatched.
		$this->assertNotNull( $async_queue );
		$this->assertNotNull( $async_group );
		$this->assertEquals( 'test_async_event', $async_queue->hook );
		$this->assertEquals( $async_group->group_id, $async_queue->group_id );
		$this->assertEquals( 'async', $async_group->slug );
		$this->assertEquals( json_encode( array( 'foo' => 'bar' ) ), $async_queue->args );
		$this->assertEquals( '0000-00-00 00:00:00', $async_queue->scheduled_date_local );

		// Dispatch delayed event
		fwrite( STDOUT, " - Dispatch delayed event \n" );
		$delayed       = $this->get_delayed_event();
		$delayed_id    = $this->action_scheduler_dispatcher->dispatch( $delayed );
		$delayed_queue = $GLOBALS['wpdb']->get_row( "SELECT * FROM {$GLOBALS['wpdb']->prefix}actionscheduler_actions WHERE action_id = $delayed_id" );
		$delayed_group = $GLOBALS['wpdb']->get_row( "SELECT * FROM {$GLOBALS['wpdb']->prefix}actionscheduler_groups WHERE group_id = {$delayed_queue->group_id}" );

		// Check was dispatched.
		$this->assertNotNull( $delayed_queue );
		$this->assertNotNull( $delayed_group );
		$this->assertEquals( 'test_delayed_event', $delayed_queue->hook );
		$this->assertEquals( $delayed_group->group_id, $delayed_queue->group_id );
		$this->assertEquals( 'delayed', $delayed_group->slug );
		$this->assertEquals( json_encode( array( 'foo' => 'bar' ) ), $delayed_queue->args );
		$this->assertEquals( '1983-02-24 00:00:00', $delayed_queue->scheduled_date_local );

		// Dispatch recurring event
		fwrite( STDOUT, " - Dispatch recurring event (without Delay) \n" );
		$recurring       = $this->get_recurring_event();
		$recurring_id    = $this->action_scheduler_dispatcher->dispatch( $recurring );
		$recurring_queue = $GLOBALS['wpdb']->get_row( "SELECT * FROM {$GLOBALS['wpdb']->prefix}actionscheduler_actions WHERE action_id = $recurring_id" );
		$recurring_group = $GLOBALS['wpdb']->get_row( "SELECT * FROM {$GLOBALS['wpdb']->prefix}actionscheduler_groups WHERE group_id = {$recurring_queue->group_id}" );

		// Check was dispatched.
		$this->assertNotNull( $recurring_queue );
		$this->assertNotNull( $recurring_group );
		$this->assertEquals( 'test_recurring_event', $recurring_queue->hook );
		$this->assertEquals( $recurring_group->group_id, $recurring_queue->group_id );
		$this->assertEquals( 'recurring', $recurring_group->slug );
		$this->assertEquals( json_encode( array( 'foo' => 'bar' ) ), $recurring_queue->args );
		$this->assertEquals( 60, \unserialize( $recurring_queue->schedule )->get_recurrence() );

		// Dispatch recurring event with delay
		fwrite( STDOUT, " - Dispatch recurring event (with Delay) \n" );
		$recurring_delayed       = $this->get_recurring_event(new DateTimeImmutable( '1983-02-24 00:00:00' ) );
		$recurring_delayed_id    = $this->action_scheduler_dispatcher->dispatch( $recurring_delayed );
		$recurring_delayed_queue = $GLOBALS['wpdb']->get_row( "SELECT * FROM {$GLOBALS['wpdb']->prefix}actionscheduler_actions WHERE action_id = $recurring_delayed_id" );
		$recurring_delayed_group = $GLOBALS['wpdb']->get_row( "SELECT * FROM {$GLOBALS['wpdb']->prefix}actionscheduler_groups WHERE group_id = {$recurring_delayed_queue->group_id}" );

		// Check was dispatched.
		$this->assertNotNull( $recurring_delayed_queue );
		$this->assertNotNull( $recurring_delayed_group );
		$this->assertEquals( 'test_recurring_event', $recurring_delayed_queue->hook );
		$this->assertEquals( $recurring_delayed_group->group_id, $recurring_delayed_queue->group_id );
		$this->assertEquals( 'recurring', $recurring_delayed_group->slug );
		$this->assertEquals( json_encode( array( 'foo' => 'bar' ) ), $recurring_delayed_queue->args );
		$this->assertEquals( 60, \unserialize( $recurring_delayed_queue->schedule )->get_recurrence() );
		$this->assertEquals( '1983-02-24 00:00:00', $recurring_delayed_queue->scheduled_date_local );

	}

	/**
	 * @testdox An exception should be thrown if an invalid event type is attempted to be dispatched.
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_throws_exception_if_invalid_event(): void {

			$invalid_event = new class()implements Event{
				public function get_group(): string {
					return 'invalid';
				}
				public function get_hook(): string {
					return 'test_invalid_event';
				}
				public function get_data(): array {
					return array( 'foo' => 'bar' );
				}
				public function delayed_until(): ?\DateTimeImmutable {
					return null;
				}
				public function interval(): ?int {
					return -9999;
				}
				public function is_unique(): bool {
					return false;
				}
			};

			$this->expectException( \Exception::class );
			$this->expectExceptionMessage( 'Unable to determine dispatch type' );
			$this->action_scheduler_dispatcher->dispatch( $invalid_event );
	}


	/**
	 * Returns an async event.
	 *
	 * @return \PinkCrab\Queue\Event\Async_Event
	 */
	private function get_async_event(): Async_Event {
		return new class() extends Async_Event{
			protected $hook  = 'test_async_event';
			protected $group = 'async';
			protected $data  = array( 'foo' => 'bar' );
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
			protected $hook     = 'test_delayed_event';
			protected $group    = 'delayed';
			protected $data     = array( 'foo' => 'bar' );
			protected $interval = 0;
			public function __construct( string $delay ) {
				$this->delay = $delay;
			}
			public function delayed_until(): ?\DateTimeImmutable {
				return new \DateTimeImmutable( $this->delay );
			}
		};

	}

	/**
	 * Returns a recurring event.
	 *
	 * @param ?DateTimeImmutable $delayed_until
	 * @return \PinkCrab\Queue\Event\Recurring_Event
	 */
	private function get_recurring_event(?\DateTimeImmutable $delayed_until = null ): Recurring_Event {
		// If we have no delayed until, do not overwrite the default.
		if ( is_null( $delayed_until ) ) {
			return new class() extends Recurring_Event
			{
				protected $hook  = 'test_recurring_event';
				protected $group = 'recurring';
				protected $data  = array( 'foo' => 'bar' );
				public function interval(): int {
					return 60;
				}
			};
		}
		
		
		return new class($delayed_until) extends Recurring_Event
		{
			protected $_delayed_until;
			protected $hook  = 'test_recurring_event';
			protected $group = 'recurring';
			protected $data  = array( 'foo' => 'bar' );
			public function __construct(?\DateTimeImmutable $delayed_until) {
				$this->_delayed_until = $delayed_until;
			}
			public function interval(): int {
				return 60;
			}
			public function delayed_until(): ?\DateTimeImmutable {
				return $this->_delayed_until;
			}

		};
	}

}
