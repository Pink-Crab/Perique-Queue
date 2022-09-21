<?php

/**
 * Unit Tests.
 *
 * Tests the registration middleware service.
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Unit\Registration_Middleware;

use PinkCrab\Loader\Hook_Loader;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\Queue\Tests\Fixture\Event\Logging_Event;
use PinkCrab\Queue\Registration_Middleware\Queue_Middleware;

/**
 * @group unit
 * @group registration_middleware
 */
class Test_Queue_Middleware extends \WP_UnitTestCase {

	/**
	 * @testdox It should be possible add a listener to the hook loader via the middleware.
	 */
	public function test_it_should_be_possible_add_a_listener_to_the_hook_loader_via_the_middleware() {

        $loader = new Hook_Loader();
		$middleware = new Queue_Middleware();
        $middleware->set_hook_loader( $loader );

        // Event
        $event = new Logging_Event();
        $middleware->process( $event );
        
        // Check hook was registered.
        $hooks = Objects::get_property( $loader, 'hooks' );
        $this->assertCount(1, $hooks);

        $hook = $hooks->pop();
        $this->assertEquals( 'pinkcrab_queue_test_event', $hook->get_handle() );
        $this->assertEquals( $event, $hook->get_callback() );
	}

}
