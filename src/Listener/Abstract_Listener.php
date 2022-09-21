<?php

declare(strict_types=1);

/**
 * Abstract Class for all Listeners
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

namespace PinkCrab\Queue\Listener;

use PinkCrab\Loader\Hook_Loader;

abstract class Abstract_Listener implements Listener {

	/**
	 * The hook name (Hook)
	 *
	 * @var string
	 */
	protected $hook;

	/**
	 * Gets the hook name
	 *
	 * @return string
	 */
	final public function get_hook(): string {
		return $this->hook;
	}

	/**
	 * Handles the callback.
	 *
	 * @return void
	 */
	final public function __invoke(): void {
		$args = func_get_args();
		$this->handle( $args );
	}

	/**
	 * Auto registers the hook.
	 *
	 * @param \PinkCrab\Loader\Hook_Loader $loader
	 * @return void
	 */
	final public function register( Hook_Loader $loader ): void {
		$loader->action( $this->hook, $this );
	}

	/**
	 * Handles the call back.
	 *
	 * @param mixed[] $args
	 * @return void
	 */
	abstract protected function handle( array $args): void;

}
