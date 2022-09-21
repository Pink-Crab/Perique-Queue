<?php

/**
 * Mock event that logs if its called via an action.
 *
 * @package PinkCrab\Queue\Tests
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Fixture\Event;

use PinkCrab\Queue\Listener\Abstract_Listener;

class Logging_Event extends Abstract_Listener{

    protected $hook = 'pinkcrab_queue_test_event';

    public static $log = [];

    /**
	 * Handles the call back.
	 *
	 * @param mixed[] $args
	 * @return void
	 */
	protected function handle( array $args): void{
        self::$log[] = $args;
    }
}