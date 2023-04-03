<?php

/**
 * Unit Tests.
 *
 * Tests the Listeners
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Unit\Module;

use PinkCrab\Queue\Tests\Fixture\Listener\Logging_Listener;

/**
 * @group unit
 * @group listener
 */
class Test_Listener extends \WP_UnitTestCase {

	/** It should be possible to get the hook name from a listener that extends the Abstract_Listener */
	public function test_it_should_be_possible_to_get_the_hook_name_from_a_listener_that_extends_the_abstract_listener() {
		$listener = new Logging_Listener();
		$listener->_set_hook( 'test_hook' );

		$this->assertEquals( 'test_hook', $listener->get_hook() );
	}

	/** @testdox If a listener has not has its hook set, an exception should be thrown */
	public function test_if_a_listener_has_not_has_its_hook_set_an_exception_should_be_thrown() {
		$this->expectException( \TypeError::class );
		$listener = new Logging_Listener();
		$listener->get_hook();
	}

	/** @testdox When a listener is invoked, its args should be passed to the handle() method */
	public function test_when_a_listener_is_invoked_its_args_should_be_passed_to_the_handle_method() {
		$listener = new Logging_Listener();
		$listener->_set_hook( 'test_hook' );

		$listener( array( 'test' => 'test' ), 'foo' );
		$this->assertEquals( 'handle[{"test":"test"},"foo"]', Logging_Listener::$log[0] );
	}

	/** @testdox When the hook loader is passed to the register method, it should be added to the loader as a invokeable callback on the defined hook */
	public function test_when_the_hook_loader_is_passed_to_the_register_method_it_should_be_added_to_the_loader_as_a_invokeable_callback_on_the_defined_hook() {
		$listener = new Logging_Listener();
		$listener->_set_hook( 'added_hook_call' );

		$loader = $this->createMock( \PinkCrab\Loader\Hook_Loader::class );
		$loader->expects( $this->once() )
			->method( 'action' )
			->with( 'added_hook_call', $listener, 1, 10 );

		$listener->register( $loader );
	}
}
