<?php

/**
 * Unit Tests.
 *
 * Tests that Events can be managed using WC Action Scheduler.
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Unit\Queue_Driver\Action_Scheduler;

use DateTime;
use DateTimeImmutable;
use PinkCrab\Queue\Event\Event;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\Queue\Event\Async_Event;
use PinkCrab\Queue\Event\Delayed_Event;
use PinkCrab\Queue\Event\Recurring_Event;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Dispatcher;

/**
 * @group unit
 * @group action_scheduler
 */
class Test_Action_Scheduler_Dispatcher extends \WP_UnitTestCase {

	/**
	 * @var Action_Scheduler_Dispatcher
	 */
	private $action_scheduler_dispatcher;

	protected function setUp(): void {
		$this->action_scheduler_dispatcher = new Action_Scheduler_Dispatcher();
	}

	/**
	 * Returns a mocked version of the dispatcher, which logs the dispatch calls and the method used.
	 *
	 * @call $this->log to access the dispatch calls.
	 * @return \PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Dispatcher
	 */
	public function get_logging_dispatcher(): Action_Scheduler_Dispatcher {
		return new class() extends Action_Scheduler_Dispatcher {
			public $log = array();
			public function dispatch( Event $event ): ?int {
				$this->log[] = array(
					'method' => Objects::invoke_method( $this, 'get_dispatch_type', array( $event ) ),
					'event'  => $event,
				);
				return 1;
			}
			public function clear() {
				$this->log = array();
			}
		};
	}

    /** @testdox It should be possible to dispatch a valid async event, either custom or extending from the abstract Async_Event */
    public function test_can_determine_async_event(): void
    {
        $dispatcher  = $this->get_logging_dispatcher();

        // Can determine from async abstract event.
		$dispatcher->dispatch( $this->createMock( Async_Event::class ) );

        $this->assertEquals( 'dispatch_async', $dispatcher->log[0]['method'] );
        $dispatcher->clear();

        // Can determine if both interval and delay until are unset.
        $dispatcher->dispatch( $this->createMock(Event::class) );

        $this->assertEquals( 'dispatch_async', $dispatcher->log[0]['method'] );
        $dispatcher->clear();
    }

    /** @testdox It should be possible to dispatch a valid delayed event, either custom or extending from the abstract Delayed_Event */
    public function test_can_determine_delayed_event(): void
    {
        $dispatcher  = $this->get_logging_dispatcher();

        // Can determine from delayed abstract event.
        $event = $this->createMock( Delayed_Event::class );
        $event->method( 'delayed_until' )->willReturn( new DateTimeImmutable() );
        $dispatcher->dispatch( $event );

        $this->assertEquals( 'dispatch_at', $dispatcher->log[0]['method'] );
        $dispatcher->clear();

        // Can determine if both interval and delay until are unset.
        $event = $this->createMock(Event::class);
        $event->method( 'delayed_until' )->willReturn( new DateTimeImmutable() );
        $dispatcher->dispatch( $event );

        $this->assertEquals( 'dispatch_at', $dispatcher->log[0]['method'] );
        $dispatcher->clear();
    }

    /** @testdox It should be possible to dispatch a valid recurring event, either custom or extending from the abstract Recurring_Event */
    public function test_can_determine_recurring_event(): void
    {
        $dispatcher  = $this->get_logging_dispatcher();

        // Can determine from recurring abstract event.
        $event = $this->createMock( Recurring_Event::class );
        $event->method( 'interval' )->willReturn( 12 );
        $dispatcher->dispatch( $event );

        $this->assertEquals( 'dispatch_recurring', $dispatcher->log[0]['method'] );
        $dispatcher->clear();

        // Can determine if both interval and delay until are unset.
        $event = $this->createMock(Event::class);
        $event->method( 'interval' )->willReturn( 12 );
        $dispatcher->dispatch( $event );

        $this->assertEquals( 'dispatch_recurring', $dispatcher->log[0]['method'] );
        $dispatcher->clear();
    }

    /** @testdox An exception should be thrown if no delayed until value is set for a delayed event */
    public function test_throws_exception_if_no_delayed_until_for_delayed_event(): void
    {
        $event = $this->createMock( Delayed_Event::class );
        $event->method( 'delayed_until' )->willReturn( null );
        $this->expectExceptionMessage('Event must have a delayed_until date/time.');
        $this->expectException( \InvalidArgumentException::class );
        Objects::invoke_method( $this->action_scheduler_dispatcher, 'dispatch_at', array( $event ) );
    }

    /** @testdox An exception should be thrown if no interval value is set for a recurring event */
    public function test_throws_exception_if_no_interval_for_recurring_event(): void
    {
        $event = $this->createMock( Recurring_Event::class );
        $event->method( 'delayed_until' )->willReturn( null );
        $event->method( 'interval' )->willReturn( null );
        $this->expectException( \InvalidArgumentException::class );
        $this->expectExceptionMessage('Event must have an for it to be recurring.');
        Objects::invoke_method( $this->action_scheduler_dispatcher, 'dispatch_recurring', array( $event ) );
    }
    
    
    
}
