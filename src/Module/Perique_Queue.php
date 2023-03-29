<?php

declare(strict_types=1);

/**
 * Bootstrap the Queue and Listeners to Perique as it boots.
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
 * @since 2.0.0
 */

namespace PinkCrab\Queue\Module;

use PinkCrab\Loader\Hook_Loader;
use PinkCrab\Queue\Listener\Listener;
use PinkCrab\Queue\Queue_Driver\Queue;
use PinkCrab\Perique\Interfaces\Module;
use PinkCrab\Perique\Application\App_Config;
use PinkCrab\Perique\Interfaces\DI_Container;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Driver;

final class Perique_Queue implements Module {

	private ?Queue $queue_driver = null;

	/**
	 * Set the Queue Driver to use.
	 *
	 * @param Queue $queue_driver
	 * @return void
	 */
	public function set_queue_driver( Queue $queue_driver ): void {
		$this->queue_driver = $queue_driver;

		// Init the queue driver.
		add_action( 'init', array( $this, 'init_driver' ), -1 );
	}

	/**
	 * Gets the queue driver.
	 *
	 * @return ?Queue
	 */
	public function get_queue_driver(): ?Queue {
		return $this->queue_driver;
	}

	/**
	 * Initialise the driver.
	 *
	 * @return void
	 */
	public function init_driver(): void {
		// If the queue driver is not set, default to the Action Scheduler Driver.
		$this->verify_queue_driver();

		$this->queue_driver->init(); // @phpstan-ignore-line, we have verified the driver is set.
	}

	/**
	 * Get the Queue Driver to use.
	 *
	 * Will default to the Action Scheduler Queue Driver, if not defined.
	 *
	 * @return void
	 */
	private function verify_queue_driver(): void {
		// If the queue driver is not set, default to the Action Scheduler Driver.
		if ( ! $this->queue_driver instanceof Queue ) {
			$this->queue_driver = Action_Scheduler_Driver::get_instance();
		}
	}

	/**
	 * Used to create the controller instance and register the hook call, to trigger.
	 *
	 * @pram App_Config $config
	 * @pram Hook_Loader $loader
	 * @pram DI_Container $container
	 * @return void
	 */
	public function pre_boot( App_Config $config, Hook_Loader $loader, DI_Container $container ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceBeforeLastUsed, Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceAfterLastUsed

		$this->verify_queue_driver();

		// Init queue setup
		$this->queue_driver->setup(); // @phpstan-ignore-line, we have verified the driver is set.

		// Set the DI Rule for the Queue Driver.
		$container->addRule(
			'*',
			array(
				'substitutions' => array( Queue::class => $this->queue_driver ),
			)
		);

		// Pass the hook loader into any listeners.
		$container->addRule(
			Listener::class,
			array(
				'call' => array(
					array( 'register', array( $loader ) ),
				),
			)
		);
	}

	/** @inheritDoc */
	public function pre_register( App_Config $config, Hook_Loader $loader, DI_Container $container ): void {} // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceBeforeLastUsed

	/** @inheritDoc */
	public function post_register( App_Config $config, Hook_Loader $loader, DI_Container $container ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceBeforeLastUsed, Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceAfterLastUsed
		// If the queue driver is not set, default to the Action Scheduler Driver.
		$this->verify_queue_driver();

		// Run the queue tear down.
		$this->queue_driver->teardown(); // @phpstan-ignore-line, we have verified the driver is set.
	}

	/** @inheritDoc */
	public function get_middleware(): ?string {
		return null;
	}
}
