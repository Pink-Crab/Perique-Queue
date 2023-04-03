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

use PinkCrab\Queue\Event\Async_Event;

class Logging_Event extends Async_Event{
    protected $hook = 'pinkcrab_queue_test_event';
}