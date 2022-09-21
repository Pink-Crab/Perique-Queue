<?php

declare(strict_types=1);

/**
 * Used to managed events within queue.
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

use stdClass;
use DateTimeImmutable;
use PinkCrab\Queue\Event\Event;
use ActionScheduler_SimpleSchedule;
use PinkCrab\Queue\Event\Async_Event;
use PinkCrab\Queue\Event\Delayed_Event;
use PinkCrab\Queue\Event\Recurring_Event;

class Action_Scheduler_Queue_Manager {

	/**
	 * Cancel queued events.
	 *
	 * @param Event $event
	 * @param bool $next_only
	 * @return void
	 */
	public function cancel( Event $event, bool $next_only = false ): void {
		if ( $next_only ) {
			\as_unschedule_action(
				$event->get_hook(),
				$event->get_data() ?? array(),
				$event->get_group()
			);
			return;
		}

		\as_unschedule_all_actions(
			$event->get_hook(),
			$event->get_data() ?? array(),
			$event->get_group()
		);
	}

	/**
	 * Finds all or the next instance of an event.
	 *
	 * @param Event $event
	 * @return DateTimeImmutable|null
	 */
	public function find_next( Event $event ): ?DateTimeImmutable {
		$next = as_next_scheduled_action(
			$event->get_hook(),
			$event->get_data() ?? array(),
			$event->get_group()
		);

		return is_int( $next )
			? ( DateTimeImmutable::createFromFormat( 'U', \strval( $next ), wp_timezone() ) ?: null )
			: null;
	}

	/**
	 * Finds all or the next instance of an event.
	 *
	 * @param Event $event
	 * @return array<int, DateTimeImmutable>
	 */
	public function find_all( Event $event ): array {
		$events = as_get_scheduled_actions(
			array(
				'hook'         => $event->get_hook(),
				'args'         => $event->get_data() ?? array(),
				'group'        => $event->get_group(),
				'status'       => \ActionScheduler_Store::STATUS_PENDING,
				'orderby'      => 'date',
				'per_page'     => -1,
				'date'         => DateTimeImmutable::createFromFormat( 'U', 'now', wp_timezone() ),
				'date_compare' => '>',
			)
		);

		return array_reduce(
			$events,
			function ( array $carry, \ActionScheduler_Action $event ): array {
				/** @var ActionScheduler_SimpleSchedule */
				$schedule = $event->get_schedule();

				// Get the date from schedule, if valid add to array.
				$date = $schedule->get_date();
				if ( $date instanceof \DateTime ) {
					$carry[] = DateTimeImmutable::createFromMutable( $date );
				}
				return $carry;
			},
			array()
		);
	}

	/**
	 * Checks if an event is queued.
	 *
	 * @param \PinkCrab\Queue\Event\Event $event
	 * @return bool
	 */
	public function exists( Event $event ): bool {
		return as_has_scheduled_action(
			$event->get_hook(),
			$event->get_data() ?? array(),
			$event->get_group()
		);
	}

}
