<?php

/**
 * Abstract Class for all Functional Tests
 *
 * Creates an instance of Perique on each test and unsets it after each test.
 *
 * @package PinkCrab\Queue\Tests\Functional
 * @author Glynn Quelch glynn.quelch@gmail.com
 * @since 0.1.0
 *
 */
namespace PinkCrab\Queue\Tests\Functional;

use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\Perique\Application\App;
use PinkCrab\Perique\Application\App_Factory;
use PinkCrab\Queue\Registration_Middleware\Queue_Middleware;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Driver;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Dispatcher;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Queue_Manager;


/**
 * @group functional
 */
abstract class Abstract_Functional_Test extends \WP_UnitTestCase {

	/** @var App */
	protected $perique;

	/** @var Action_Scheduler_Driver */
	protected $queue;

	/** @var Action_Scheduler_Dispatcher */
	protected $action_scheduler_dispatcher;

    /** @var Action_Scheduler_Queue_Manager */
    protected $action_scheduler_queue_manager;

	protected function setUp(): void {

		$this->action_scheduler_dispatcher = new Action_Scheduler_Dispatcher();

        $this->action_scheduler_queue_manager = new Action_Scheduler_Queue_Manager();

		$this->queue = new Action_Scheduler_Driver( $this->action_scheduler_dispatcher, $this->action_scheduler_queue_manager );
		$this->queue->setup();

		$this->perique = ( new App_Factory( __DIR__ ) )
            ->with_wp_dice( true )
			->di_rules( array() )
			->registration_classes( array() )
			->construct_registration_middleware( Queue_Middleware::class )
			->boot();

		do_action( 'init' );
		do_action( 'plugins_loaded' );
	}

	/**
	 * Unsets the instance of Perique.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		// Mark as not included.
		Objects::set_property( $this->queue, 'included', false );
		
		Objects::set_property( $this->perique, 'app_config', null );
		Objects::set_property( $this->perique, 'container', null );
		Objects::set_property( $this->perique, 'registration', null );
		Objects::set_property( $this->perique, 'loader', null );
		Objects::set_property( $this->perique, 'booted', false );
		$this->perique = null;

		// Empty the action scheduler queue.
		$table_prefix = $GLOBALS['wpdb']->prefix;
		$GLOBALS['wpdb']->query( "DELETE FROM {$table_prefix}actionscheduler_actions" );
		$GLOBALS['wpdb']->query( "DELETE FROM {$table_prefix}actionscheduler_claims" );
		$GLOBALS['wpdb']->query( "DELETE FROM {$table_prefix}actionscheduler_groups" );
		$GLOBALS['wpdb']->query( "DELETE FROM {$table_prefix}actionscheduler_logs" );
	}

}
