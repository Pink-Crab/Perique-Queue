<?php

/**
 * Functional Tests.
 *
 * The bootstrap for setting up the lib with perique
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Functional;

use PinkCrab\Queue\Queue_Bootstrap;
use PinkCrab\Perique\Application\App;
use PinkCrab\Queue\Queue_Driver\Queue;
use PinkCrab\Perique\Application\Hooks;

/**
 * @group functional
 * @group setup
 */
class Test_Queue_Bootstrap extends Abstract_Functional_Test {

	/**
	 * @testdox It should be possible to setup the queues driver to Perique in a simple boot static method.
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_can_setup_queue_driver(): void {
		$results = array();
		$driver  = $this->createMock( Queue::class );
		$driver->method( 'setup' )->will(
			$this->returnCallback(
				function() use ( &$results ) {
					$results[] = 'setup';
				}
			)
		);
        $driver->method( 'init' )->will(
			$this->returnCallback(
				function() use ( &$results ) {
					$results[] = 'init';
				}
			)
		);
		$driver->method( 'teardown' )->will(
			$this->returnCallback(
				function() use ( &$results ) {
					$results[] = 'teardown';
				}
			)
		);

		// Boot the Queue
		Queue_Bootstrap::init( $driver );

        // Setup should be called straight away.
        $this->assertCount( 1, $results );
        $this->assertEquals( 'setup', $results[0] );

		// On run tare down manually first as its called on init.
		\do_action( 'PinkCrab/App/Boot/post_registration' );
		$this->assertEquals( array( 'setup','teardown' ), $results );
		$this->assertCount( 2, $results );

		// Calling init will now run the init and the teardown again
		\do_action( 'init' );
		$this->assertEquals( array( 'setup','teardown', 'init', 'teardown' ), $results );
		$this->assertCount( 4, $results );

        // Ensure the DI Rule is added for Queue to be used as a depenedency.
        $rules = \apply_filters( Hooks::APP_INIT_SET_DI_RULES, [] );
        $this->assertArrayHasKey('*', $rules);
        $this->assertArrayHasKey(Queue::class, $rules['*']['substitutions']);
        $this->assertEquals($driver, $rules['*']['substitutions'][Queue::class]);
	}
}
