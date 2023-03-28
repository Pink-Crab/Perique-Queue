<?php

declare(strict_types=1);

/**
 * Injectable Queue Service for interacting with the Queue.
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
use PinkCrab\Queue\Queue_Driver\Queue;

final class Queue_Service {

	private Queue $queue;

	public function __construct( Queue $queue ) {
		$this->queue = $queue;
	}

	/**
	 * Dispatch Event
	 *
	 * @param Event $event
	 */
	public function dispatch( Event $event ): ?int {
		return $this->queue->dispatch( $event );
	}

	/**
	 * Cancel next occurrence of Event
	 *
	 * @param Event $event
	 */
	public function cancel_next( Event $event ): void {
		$this->queue->cancel_next( $event );
	}

	/**
	 * Cancel all occurrences of Event
	 *
	 * @param Event $event
	 */
	public function cancel_all( Event $event ): void {
		$this->queue->cancel_all( $event );
	}

	/**
	 * Get next occurrence of Event
	 *
	 * @param Event $event
	 * @return DateTimeImmutable|null
	 */
	public function next( Event $event ): ?DateTimeImmutable {
		return $this->queue->find_next( $event );
	}

	/**
	 * Get all occurrences of Event
	 *
	 * @param Event $event
	 * @return DateTimeImmutable[]
	 */
	public function all( Event $event ): array {
		return $this->queue->find_all( $event );
	}


	/**
	 * Checks if an event exists.
	 *
	 * @param Event $event
	 * @return bool
	 */
	public function exists( Event $event ): bool {
		return $this->queue->is_scheduled( $event );
	}
}
