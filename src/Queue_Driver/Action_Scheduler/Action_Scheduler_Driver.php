<?php

declare(strict_types=1);

/**
 * The Queue Driver for WC Action Scheduler.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\Queue
 * @since 0.1.0
 */

namespace PinkCrab\Queue\Queue_Driver\Action_Scheduler;

use DateTimeImmutable;
use ActionScheduler_Store;
use PinkCrab\Queue\Event\Event;
use PinkCrab\Queue\Queue_Driver\Queue;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Dispatcher;

class Action_Scheduler_Driver implements Queue {

	/**
	 * The event dispatcher
	 *
	 * @var Action_Scheduler_Dispatcher
	 */
	private $dispatcher;

	/** @var Action_Scheduler_Queue_Manager */
	private $queue_manager;

	/** @var bool */
	private static $included = false;

	public function __construct( Action_Scheduler_Dispatcher $dispatcher, Action_Scheduler_Queue_Manager $queue_manager ) {
		$this->dispatcher    = $dispatcher;
		$this->queue_manager = $queue_manager;
	}

	/**
	 * Setup the Action Scheduler queue driver.
	 *
	 * @return Action_Scheduler_Driver
	 */
	public static function get_instance(): self {
		$instance = new self(
			new Action_Scheduler_Dispatcher(),
			new Action_Scheduler_Queue_Manager()
		);
		$instance->setup();
		return $instance;
	}

	/**
	 * Is run before the driver is initialized.
	 *
	 * @return void
	 */
	public function setup(): void {

		// If the lib has already been included, bail.
		if ( self::$included ) {
			return;
		}

		// Path to Action Scheduler plugin directory.
		$path = join(
			\DIRECTORY_SEPARATOR,
			array(
				dirname( __FILE__, 4 ),
				'lib',
				'action-scheduler',
				'action-scheduler.php',
			)
		);

		// Filter the path to the action-scheduler path.
		$path = apply_filters( 'pinkcrab_queue_action_scheduler_path', $path );

		// Check if the file exists.
		if ( ! file_exists( $path ) ) {
			throw new \RuntimeException( 'Action Scheduler is not installed.' );
		}

		// Include the Action Scheduler plugin.
		require_once $path;

		// Mark as included.
		self::$included = true;
	}

	/**
	 * Is run after the driver is initialized.
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	public function teardown(): void {
		// noOp
	}

	/**
	 * Initialise the driver.
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	public function init(): void {
		// noOp
	}

	/**
	 * Dispatch Event
	 *
	 * @param Event $event
	 */
	public function dispatch( Event $event ): ?int {
		$result = $this->dispatcher->dispatch( $event );
		return \is_int( $result ) ? $result : null;
	}

	/**
	 * Cancel next occurrence of Event
	 *
	 * @param Event $event
	 */
	public function cancel_next( Event $event ): void {
		$this->queue_manager->cancel( $event, true );
	}

	/**
	 * Cancel all occurrences of Event
	 *
	 * @param Event $event
	 */
	public function cancel_all( Event $event ): void {
		$this->queue_manager->cancel( $event, false );
	}

	/**
	 * Get next occurrence of Event
	 *
	 * @param Event $event
	 * @return DateTimeImmutable|null
	 */
	public function find_next( Event $event ): ?DateTimeImmutable {
		return $this->queue_manager->find_next( $event );
	}

	/**
	 * Get all occurrences of Event
	 *
	 * @param Event $event
	 * @return DateTimeImmutable[]
	 */
	public function find_all( Event $event ): array {
		return $this->queue_manager->find_all( $event );
	}


	/**
	 * Checks if an event is scheduled.
	 *
	 * @param Event $event
	 * @return bool
	 */
	public function is_scheduled( Event $event ): bool {
		return $this->queue_manager->exists( $event );
	}
}
