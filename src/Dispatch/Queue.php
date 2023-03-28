<?php

declare(strict_types=1);

/**
 * Proxy Class for accessing the queue.
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

namespace PinkCrab\Queue\Dispatch;

use DateTimeImmutable;
use PinkCrab\Queue\Event\Event;
use PinkCrab\Perique\Application\App;

final class Queue {

	private static ?Queue_Service $instance = null;

	/** Gets the queue service
	 * @return Queue_Service
	 */
	public static function service(): Queue_Service {
		if ( self::$instance === null ) {
			self::$instance = App::make( Queue_Service::class );
		}

		// If we have not set the instance, we have a problem.
		if ( self::$instance === null ) {
			throw new \RuntimeException( 'Unable to load Queue_Service' );
		}

		return self::$instance;
	}

	/**
	 * Dispatch Event
	 *
	 * @param Event $event
	 * @return int|null
	 */
	public static function dispatch( Event $event ): ?int {
		return self::service()->dispatch( $event );
	}
}
