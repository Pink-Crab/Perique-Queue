<?php

/**
 * Unit Tests for the Queue Service
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Unit\Dispatch;

use DateTime;
use DateTimeImmutable;
use PinkCrab\Queue\Event\Event;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\Queue\Event\Async_Event;
use PinkCrab\Queue\Queue_Driver\Queue;
use PinkCrab\Queue\Event\Delayed_Event;
use PinkCrab\Queue\Event\Recurring_Event;
use PinkCrab\Queue\Dispatch\Queue_Service;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Dispatcher;

/**
 * @group unit
 * @group dispatch
 */
class Test_Queue_Service extends \WP_UnitTestCase {

	/** @testdox It should be possible to dispatch an event using the Queue_Service and have the underlying Driver handle the dispatching */
	public function test_can_dispatch_event(): void {

		$driver = $this->createMock( Queue::class );
		$driver->method( 'dispatch' )->willReturn( 1 );

		$queue_service = new Queue_Service( $driver );
		$this->assertEquals( 1, $queue_service->dispatch( $this->createMock( Async_Event::class ) ) );
	}

	/** @testdox It should be possible to cancel the next queued event and have the underlying driver handled the request */
	public function test_can_cancel_next_event(): void {

		$called = array();
		$driver = $this->createMock( Queue::class );
		$driver->method( 'cancel_next' )->willReturnCallback(
			function( Async_Event $event ) use ( &$called ) {
				$called[] = $event;
			}
		);

		$event = $this->createMock( Async_Event::class );

		$queue_service = new Queue_Service( $driver );
		$queue_service->cancel_next( $event );

		$this->assertEquals( $event, $called[0] );
	}

	/** @testdox It should be possible to cancel all queued events and have the underlying driver handled the request */
	public function test_can_cancel_all_events(): void {

		$called = array();
		$driver = $this->createMock( Queue::class );
		$driver->method( 'cancel_all' )->willReturnCallback(
			function( Async_Event $event ) use ( &$called ) {
				$called[] = $event;
			}
		);

		$event = $this->createMock( Async_Event::class );

		$queue_service = new Queue_Service( $driver );
		$queue_service->cancel_all( $event );

		$this->assertEquals( $event, $called[0] );
	}

	/** @testdox It should be possible to get the next events DateTime in the queued events and have the underlying driver handled the request */
	public function test_can_get_next_event_datetime(): void {
		$driver = $this->createMock( Queue::class );
		$driver->method( 'find_next' )->willReturnCallback(
			function( Async_Event $event ): ?DateTimeImmutable {
				return null;
			}
		);

		$event = $this->createMock( Async_Event::class );

		$queue_service = new Queue_Service( $driver );
		$this->assertNull( $queue_service->next( $event ) );
	}

    /** @testdox It should be possible to get all events DateTime in the queued events and have the underlying driver handled the request */
    public function test_can_get_all_event_datetime(): void {
        $driver = $this->createMock( Queue::class );
        $driver->method( 'find_all' )->willReturnCallback(
            function( Async_Event $event ): array {
                return array();
            }
        );

        $event = $this->createMock( Async_Event::class );

        $queue_service = new Queue_Service( $driver );
        $this->assertEmpty( $queue_service->all( $event ) );
    }

    /** @testdox It should be possible to check if an existing event is already in the queued events and have the underlying driver handled the request */
    public function test_can_check_if_event_exists(): void {
        $driver = $this->createMock( Queue::class );
        $driver->method( 'is_scheduled' )->willReturnCallback(
            function( Async_Event $event ): bool {
                return false;
            }
        );

        $event = $this->createMock( Async_Event::class );

        $queue_service = new Queue_Service( $driver );
        $this->assertFalse( $queue_service->exists( $event ) );
    }
}
