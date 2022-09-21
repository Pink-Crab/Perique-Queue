<?php

declare(strict_types=1);

/**
 * Used to dispatch an event based on its type.
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
use PinkCrab\Queue\Event\Event;
use PinkCrab\Queue\Event\Async_Event;
use PinkCrab\Queue\Event\Delayed_Event;
use PinkCrab\Queue\Event\Recurring_Event;

class Action_Scheduler_Dispatcher {

	/**
	 * Dispatch Event
	 *
	 * @param Event $event
	 * @return int|null Returns the event id.
	 */
	public function dispatch( Event $event ): ?int {
		// @phpstan-ignore-next-line
		$queue_id = \call_user_func( array( $this, $this->get_dispatch_type( $event ) ), $event );
		return \is_int( $queue_id ) ? $queue_id : null;
	}

	/**
	 * Get the dispatch type based on event values.
	 *
	 * @param Event $event
	 * @return string
	 */
	private function get_dispatch_type( Event $event ): string {
		// If the event is async or there is no delay or interval.
		if ( $event instanceof Async_Event
		|| ( is_null( $event->delayed_until() ) && is_null( $event->interval() ) )
		) {
			// Return async.
			return 'dispatch_async';
		}

		// If the event is delayed or has no interval.
		if ( $event instanceof Delayed_Event
		|| ( $event->delayed_until() instanceof DateTimeImmutable && is_null( $event->interval() ) )
		) {
			// Return delayed.
			return 'dispatch_at';
		}

		// If the event is a recurring event and has an interval.
		if ( $event instanceof Recurring_Event
		|| ( is_int( $event->interval() ) && $event->interval() > 0 )
		) {
			// Return recurring.
			return 'dispatch_recurring';
		}

		// If we get to the end and none has passed, throw exception.
		throw new \Exception( 'Unable to determine dispatch type.' );
	}

	/**
	 * Dispatches the event to the queue immediately.
	 *
	 * @param Event $event
	 * @return int Returns the event id.
	 */
	private function dispatch_async( Event $event ): int {
		return \as_enqueue_async_action(
			$event->get_hook(),
			$event->get_data() ?? array(),
			$event->get_group(),
			$event->is_unique()
		);
	}

	/**
	 * Dispatches the event to the queue at a specific time.
	 *
	 * @param Event $event
	 * @return int Returns the event id.
	 */
	private function dispatch_at( Event $event ): int {
		if ( ! $event->delayed_until() instanceof DateTimeImmutable ) {
			throw new \InvalidArgumentException( 'Event must have a delayed_until date/time.' );
		}

		/** @var DateTimeImmutable */
		$delayed_until = $event->delayed_until();

		return as_schedule_single_action(
			(int) $delayed_until->setTimezone( wp_timezone() )->format( 'U' ),
			$event->get_hook(),
			$event->get_data() ?? array(),
			$event->get_group(),
			$event->is_unique()
		);
	}

	/**
	 * Dispatches a recurring event to the queue.
	 *
	 * @param Event $event
	 * @return int Returns the event id.
	 */
	private function dispatch_recurring( Event $event ): int {
		if ( $event->interval() === null ) {
			throw new \InvalidArgumentException( 'Event must have an for it to be recurring.' );
		}

		/** @var DateTimeImmutable */
		$delayed_until = $event->delayed_until() instanceof DateTimeImmutable
			? $event->delayed_until()->setTimezone( wp_timezone() )
			: DateTimeImmutable::createFromFormat( 'U', '0', wp_timezone() );

		return as_schedule_recurring_action(
			(int) $delayed_until->format( 'U' ),
			$event->interval(),
			$event->get_hook(),
			$event->get_data() ?? array(),
			$event->get_group(),
			$event->is_unique()
		);
	}


}
