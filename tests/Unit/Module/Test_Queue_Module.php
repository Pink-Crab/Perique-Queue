<?php

/**
 * Unit Tests.
 *
 * Tests the perique queue service.
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Unit\Module;

use PinkCrab\Loader\Hook_Loader;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\Queue\Listener\Listener;
use PinkCrab\Queue\Queue_Driver\Queue;
use PinkCrab\Queue\Module\Perique_Queue;
use PinkCrab\Perique\Application\App_Config;
use PinkCrab\Perique\Interfaces\DI_Container;
use PinkCrab\Queue\Tests\Fixture\Driver\Logging_Driver;

/**
 * @group unit
 * @group module
 */
class Test_Queue_Module extends \WP_UnitTestCase {

	public function tearDown(): void {
		parent::tearDown();
		// Clear the init hook for next test.
		if ( \array_key_exists( -1, $GLOBALS['wp_filter']['init']->callbacks ) ) {
			unset( $GLOBALS['wp_filter']['init']->callbacks[-1] );
		}
		Logging_Driver::$log = array();
	}

	
	/** @testdox It should be possible to define a custom Queue driver to the module and have init added as a hook. */
	public function test_it_should_be_possible_to_define_a_custom_queue_driver_to_the_module_and_have_that_called() {
		$driver = new Logging_Driver();

		$module = new Perique_Queue();
		$module->set_queue_driver( $driver );

		// Driver should be set.
		$this->assertSame( $driver, $module->get_queue_driver() );

		// Should have added init hook on -1
		$init_hook = $GLOBALS['wp_filter']['init']->callbacks[-1];
		$this->assertCount( 1, $init_hook );

		// Should have called the init_driver method.
		$init_hook = \array_pop( $init_hook );

		$this->assertInstanceOf( Perique_Queue::class, $init_hook['function'][0] );
		$this->assertEquals( 'init_driver', $init_hook['function'][1] );
	}

	/** @testdox When the Queue Modules Init_driver method is called (via init hook), the internal Drivers init() method should also be called */
	public function test_when_the_queue_modules_init_driver_method_is_called_via_init_hook_the_internal_drivers_init_method_should_also_be_called() {
		$driver = new Logging_Driver();

		$module = new Perique_Queue();
		$module->set_queue_driver( $driver );

		// Call the init_driver method
		$module->init_driver();

		// Drivers init method should have been called.
		$this->assertContains( 'init', Logging_Driver::$log );
	}

	/** @testdox If no driver is supplied to the module, it should default to the Action Scheduler once its verified. */
	public function test_if_no_driver_is_supplied_to_the_module_it_should_default_to_the_action_scheduler() {
		$module = new Perique_Queue();

		// Should be null, until verified.
		$this->assertNull( $module->get_queue_driver() );

		// Call the internal verify method.
		Objects::invoke_method( $module, 'verify_queue_driver' );
		$this->assertInstanceOf( \PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Driver::class, $module->get_queue_driver() );
	}

	/** @testdox The module should not define any Registration Middleware */
	public function test_the_module_should_not_define_any_registration_middleware() {
		$module = new Perique_Queue();
		$this->assertNull( $module->get_middleware() );
	}

	/** @testdox The drivers teardown method should be called on the modules post_register callback */
	public function test_the_drivers_teardown_method_should_be_called_on_the_modules_post_register_callback() {
		$driver = new Logging_Driver();

		$module = new Perique_Queue();
		$module->set_queue_driver( $driver );

		// Call the init_driver method
		$module->post_register(
			new App_Config(),
			$this->createMock( Hook_Loader::class ),
			$this->createMock( DI_Container::class )
		);

		// Drivers init method should have been called.
		$this->assertContains( 'teardown', Logging_Driver::$log );
	}

	/** @testdox The DI rules for the Queue Module should be added on the pre_boot callback */
	public function test_the_di_rules_for_the_queue_module_should_be_added_on_the_pre_boot_callback() {
		$driver = new Logging_Driver();

		$module = new Perique_Queue();
		$module->set_queue_driver( $driver );

		// Create dependencies and call.
		$hook_loader     = new Hook_Loader();
		
        $container_calls = array();
		$container       = $this->createMock( DI_Container::class );
		$container->method( 'addRule' )->willReturnCallback(
			function( $rule, $args ) use ( &$container, &$container_calls ): DI_Container {
				$container_calls[ $rule ] = $args;
				return $container;
			}
		);

		// Run the pre_boot method.
		$module->pre_boot( new App_Config(), $hook_loader, $container );

		// Drivers setup() method should have been called.
		$this->assertContains( 'setup', Logging_Driver::$log );

		// Di container should contain the global substitution.
		$this->assertArrayHasKey( '*', $container_calls );
		$this->assertArrayHasKey( 'substitutions', $container_calls['*'] );
		$this->assertArrayHasKey( Queue::class, $container_calls['*']['substitutions'] );
		$this->assertSame( $driver, $container_calls['*']['substitutions'][ Queue::class ] );

		// The Listener should have been added to the hook loader.
		$this->assertArrayHasKey( Listener::class, $container_calls );
		$this->assertArrayHasKey( 'call', $container_calls[ Listener::class ] );
		$this->assertEquals( 'register', $container_calls[ Listener::class ]['call'][0][0] );
		$this->assertEquals( $hook_loader, $container_calls[ Listener::class ]['call'][0][1][0] );
	}
}
