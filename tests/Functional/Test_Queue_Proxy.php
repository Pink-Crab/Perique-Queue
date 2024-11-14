<?php

/**
 * Functional Tests.
 *
 * Tests of the Queue Proxy class
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 2.0.0
 *
 */
namespace PinkCrab\Queue\Tests\Functional;

use DateTimeImmutable;
use PinkCrab\Queue\Dispatch\Queue;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\Queue\Event\Async_Event;
use PinkCrab\Queue\Event\Delayed_Event;
use PinkCrab\Queue\Tests\Functional\Abstract_Functional_Test;

/**
 * @group functional
 * @group queue_proxy
 */
class Test_Queue_Proxy extends Abstract_Functional_Test {

    /** @testdox It should be possible to access the Queue_Service from the proxy */
    public function test_can_access_queue_service_from_proxy(): void {
       $this->assertInstanceOf( \PinkCrab\Queue\Dispatch\Queue_Service::class, Queue::service() );
    }

    /** @testdox It should be possible to dispatch and event using the Queue Proxy */
    public function test_can_dispatch_event_using_proxy(): void {
        $event = new class() extends Async_Event {
            protected $hook = 'test_can_dispatch_event_using_proxy';
        };

        $id = Queue::dispatch( $event );

        $events = $this->get_events( 'test_can_dispatch_event_using_proxy', 'pending' );

        // Check if event in is database.
        $this->assertCount( 1, $events );
        $this->assertEquals( $id, (int) $events[0]->action_id );
    }

    /** @testdox It should be possible to cancel the next instance an event using the Queue Proxy */
    public function test_can_cancel_next_event_using_proxy(): void {
        $event = new class() extends Async_Event {
            protected $hook = 'test_can_cancel_next_event_using_proxy';
        };

        // Dispatch twice.
        Queue::dispatch( $event );
        Queue::dispatch( $event );

        // Cancel Next
        Queue::cancel_next( $event );

        // Check cancelled.
        $this->assertCount( 1, $this->get_events( 'test_can_cancel_next_event_using_proxy', 'pending' ) );
        $this->assertCount( 1, $this->get_events( 'test_can_cancel_next_event_using_proxy', 'canceled' ) );
    }

    /** @testdox It should be possible to cancel all instances of an event using the Queue Proxy */
    public function test_can_cancel_all_events_using_proxy(): void {
        $event = new class() extends Async_Event {
            protected $hook = 'test_can_cancel_all_events_using_proxy';
        };

        // Dispatch twice.
        Queue::dispatch( $event );
        Queue::dispatch( $event );

        // Cancel Next
        Queue::cancel_all( $event );

        // Check cancelled.
        $this->assertCount( 0, $this->get_events( 'test_can_cancel_all_events_using_proxy', 'pending' ) );
        $this->assertCount( 2, $this->get_events( 'test_can_cancel_all_events_using_proxy', 'canceled' ) );
    }

    /** @testdox It should be possible to get the time of the next instances of an event using the Queue Proxy */
    public function test_can_get_next_event_time_using_proxy(): void {
        $event1 = new class() extends Delayed_Event {
            protected $hook = 'test_can_get_next_event_time_using_proxy';
            public function delayed_until(): ?DateTimeImmutable {
				return new DateTimeImmutable( '2025-02-24 00:00:00' );
			}
        };

        $event2 = new class() extends Delayed_Event {
            protected $hook = 'test_can_get_next_event_time_using_proxy';
            public function delayed_until(): ?DateTimeImmutable {
				return new DateTimeImmutable( '2024-02-24 00:00:00' );
			}
        };

        Queue::dispatch( $event1 );
        Queue::dispatch( $event2 );

        // Get next time.
        $next_time = Queue::next( $event1 );

        // Check results
        $this->assertInstanceOf( DateTimeImmutable::class, $next_time );
        $this->assertEquals( '2024-02-24 00:00:00', $next_time->format( 'Y-m-d H:i:s' ) );
    }

    /** @testdox It should be possible to get the time of the all of the instances of an event using the Queue Proxy */
    public function test_can_get_all_event_times_using_proxy(): void {
       $event1 = new class() extends Delayed_Event {
            protected $hook = 'test_can_get_next_event_time_using_proxy';
            public function delayed_until(): ?DateTimeImmutable {
				return new DateTimeImmutable( '2085-02-24 00:00:00' );
			}
        };

        $event2 = new class() extends Delayed_Event {
            protected $hook = 'test_can_get_next_event_time_using_proxy';
            public function delayed_until(): ?DateTimeImmutable {
				return new DateTimeImmutable( '2084-02-24 00:00:00' );
			}
        };

        Queue::dispatch( $event1 );
        Queue::dispatch( $event2 );

        $all = Queue::all( $event1 );

        // Check results
        $this->assertCount( 2, $all );

        $this->assertInstanceOf( DateTimeImmutable::class, $all[0] );
        $this->assertEquals( '2085-02-24 00:00:00', $all[1]->format( 'Y-m-d H:i:s' ) );

        $this->assertInstanceOf( DateTimeImmutable::class, $all[1] );
        $this->assertEquals( '2084-02-24 00:00:00', $all[0]->format( 'Y-m-d H:i:s' ) );
    }

    /** @testdox It should be possible to check if an event exists using the Queue Proxy */
    public function test_can_check_if_event_exists_using_proxy(): void {
        $event = new class() extends Async_Event {
            protected $hook = 'test_can_check_if_event_exists_using_proxy';
        };

        // Should not exist.
        $this->assertFalse( Queue::exists( $event ) );

        Queue::dispatch( $event );

        // Check exists.
        $this->assertTrue( Queue::exists( $event ) );
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