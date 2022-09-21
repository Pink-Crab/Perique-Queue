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
 * @since 0.1.0
 */

namespace PinkCrab\Queue;

use PinkCrab\Queue\Queue_Driver\Queue;
use PinkCrab\Perique\Application\Hooks;

class Queue_Bootstrap {

	/**
	 * Initialises the Queue and Listeners.
	 *
	 * @param Queue $queue_driver The Queue Driver.
	 * @return void
	 */
	public static function init( Queue $queue_driver ): void {
		$queue_driver->setup();

		// initialize the queue driver
		add_action(
			'init',
			function() use ( $queue_driver ) {
				$queue_driver->init();
			},
			-1
		);

		add_filter(
			Hooks::APP_INIT_SET_DI_RULES,
			function( $rules ) use ( $queue_driver ) {

				$rules['*'] = array(
					'substitutions' => array(
						Queue::class => $queue_driver,

					),
				);

				return $rules;
			}
		);

		add_action(
			HOOKS::APP_INIT_POST_REGISTRATION,
			function() use ( $queue_driver ) {
				$queue_driver->teardown();
			}
		);

	}

}
