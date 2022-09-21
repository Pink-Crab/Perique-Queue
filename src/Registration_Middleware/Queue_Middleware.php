<?php

declare(strict_types=1);

/**
 * Queue Middleware Instance
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

namespace PinkCrab\Queue\Registration_Middleware;

use PinkCrab\Loader\Hook_Loader;
use PinkCrab\Queue\Listener\Listener;
use PinkCrab\Perique\Interfaces\DI_Container;
use PinkCrab\Queue\Listener\Abstract_Listener;
use PinkCrab\Perique\Interfaces\Registration_Middleware;

class Queue_Middleware implements Registration_Middleware {

	/** @var Hook_Loader */
	protected $loader;

	/** @var DI_Container */
	protected $container;

	/**
	 * Sets the global hook loader
	 *
	 * @param \PinkCrab\Loader\Hook_Loader $loader
	 * @return void
	 */
	public function set_hook_loader( Hook_Loader $loader ) {
		$this->loader = $loader;
	}

	/**
	 * Sets the global DI containers
	 *
	 * @param \PinkCrab\Perique\Interfaces\DI_Container $container
	 * @return void
	 */
	public function set_di_container( DI_Container $container ): void {
		$this->container = $container;
	}

	/**
	 * Register all valid Listener.
	 *
	 * @param object|Listener $class
	 * @return object
	 */
	public function process( $class ) {
		if ( $class instanceof Listener ) {
			$class->register( $this->loader );
		}

		return $class;
	}



	public function setup(): void {
		/*noOp*/
	}

	/**
	 * Register all routes with WordPress calls.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		/*noOp*/
	}
}
