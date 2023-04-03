<?php

/**
 * Mock Queue Listener that logs all method calls.
 *
 * @package PinkCrab\Queue\Tests
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Fixture\Listener;

use PinkCrab\Queue\Listener\Abstract_Listener;

class Logging_Listener extends Abstract_Listener {

	public static $log = array();

	/**
	 * Sets the hook.
	 * 
	 * ONLY FOR TESTING
	 */
	public function _set_hook( $value ): void {
		$this->hook = $value;
	}

	public function handle( array $args ): void {
		self::$log[] = 'handle' . \json_encode( $args );
	}

}
