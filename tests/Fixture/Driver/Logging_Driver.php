<?php

/**
 * Mock Queue Driver that logs all method calls.
 *
 * @package PinkCrab\Queue\Tests
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Fixture\Driver;

use DateTimeImmutable;
use PinkCrab\Queue\Event\Event;
use PinkCrab\Queue\Queue_Driver\Queue;

class Logging_Driver implements Queue {

	public static $log = array();

	public function setup(): void {
		self::$log[] = 'setup';
	}

	public function init(): void {
		self::$log[] = 'init';
	}

	public function teardown(): void {
		self::$log[] = 'teardown';
	}

		/**
	 * Dispatch Event
	 *
	 * @param Event $event
	 * @return int|null
	 */
	public function dispatch( Event $event ) : ?int {
		self::$log[] = 'dispatch' . get_class( $event );
		return 1;
	}

	/**
	 * Cancel next occurrence of Event
	 *
	 * @param Event $event
	 */
	public function cancel_next( Event $event ): void {
		self::$log[] = 'cancel next' . get_class( $event );
	}

	/**
	 * Cancel all occurrences of Event
	 *
	 * @param Event $event
	 */
	public function cancel_all( Event $event ): void {
		self::$log[] = 'cancel all' . get_class( $event );
	}

	/**
	 * Get next occurrence of Event
	 *
	 * @param Event $event
	 * @return DateTimeImmutable|null
	 */
	public function find_next( Event $event ): ?DateTimeImmutable {
		self::$log[] = 'find_next' . get_class( $event );
		return null;
	}

	/**
	 * Get all occurrences of Event
	 *
	 * @param Event $event
	 * @return DateTimeImmutable[]
	 */
	public function find_all( Event $event ): array {
		self::$log[] = 'find_all' . get_class( $event );
		return array();
	}


	/**
	 * Checks if an event is scheduled.
	 *
	 * @param Event $event
	 * @return bool
	 */
	public function is_scheduled( Event $event ): bool {
		self::$log[] = 'is_scheduled' . get_class( $event );
        return false;
	}


}
