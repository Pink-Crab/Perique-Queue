<?php

/**
 * Unit Tests.
 *
 * Tests for the abstract events.
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Unit\Event;

use PinkCrab\Queue\Event\Async_Event;
use PinkCrab\Queue\Event\Delayed_Event;
use PinkCrab\Queue\Event\Recurring_Event;


/**
 * @group unit
 * @group event
 */
class Test_Abstract_Events extends \WP_UnitTestCase {

	/** @testdox It should be possible to have an Async class, without a delay. */
	public function test_it_should_be_possible_to_have_an_async_class_without_a_delay() {
		$event = new class() extends Async_Event {
			protected $group     = 'test';
			protected $hook      = 'test';
			protected $data      = array( 'test' => 'test' );
			protected $is_unique = true;
		};
		$this->assertEquals( 'test', $event->get_group() );
		$this->assertEquals( 'test', $event->get_hook() );
		$this->assertEquals( array( 'test' => 'test' ), $event->get_data() );
		$this->assertTrue( $event->is_unique() );
		$this->assertNull( $event->delayed_until() );
		$this->assertNull( $event->interval() );
	}

	/** @testdox It should be possible to have an Delayed class, with a delay. */
	public function test_it_should_be_possible_to_have_an_delayed_class_with_a_delay() {
		$event = new class() extends Delayed_Event {
			protected $group     = 'test';
			protected $hook      = 'test';
			protected $data      = array( 'test' => 'test' );
			protected $is_unique = true;
			public function delayed_until(): \DateTimeImmutable {
				return new \DateTimeImmutable( '01-01-2020' );
			}
		};
		$this->assertEquals( 'test', $event->get_group() );
		$this->assertEquals( 'test', $event->get_hook() );
		$this->assertEquals( array( 'test' => 'test' ), $event->get_data() );
		$this->assertTrue( $event->is_unique() );
		$this->assertEquals(
			( new \DateTimeImmutable( '01-01-2020' ) )->format( 'DD-MM-YYYY' ),
			$event->delayed_until()->format( 'DD-MM-YYYY' )
		);
		$this->assertNull( $event->interval() );
	}

	/** @testdox It should be possible to have an Recurring class, with a delay and interval. */
	public function test_it_should_be_possible_to_have_an_recurring_class_with_a_delay_and_interval() {
		$event = new class() extends Recurring_Event {
			protected $group     = 'test';
			protected $hook      = 'test';
			protected $data      = array( 'test' => 'test' );
			protected $is_unique = true;
			public function delayed_until(): \DateTimeImmutable {
				return new \DateTimeImmutable( '01-01-2020' );
			}
			public function interval(): int {
				return 11;
			}
		};
		$this->assertEquals( 'test', $event->get_group() );
		$this->assertEquals( 'test', $event->get_hook() );
		$this->assertEquals( array( 'test' => 'test' ), $event->get_data() );
		$this->assertTrue( $event->is_unique() );
		$this->assertEquals(
			( new \DateTimeImmutable( '01-01-2020' ) )->format( 'DD-MM-YYYY' ),
			$event->delayed_until()->format( 'DD-MM-YYYY' )
		);
		$this->assertEquals( 11, $event->interval() );
	}

}
