<?php

declare(strict_types=1);

/**
 * Queue Driver Interface
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

namespace PinkCrab\Queue\Queue_Driver;

use DateTimeImmutable;
use PinkCrab\Queue\Event\Event;

interface Queue {

	/**
	 * Is run before the driver is initialized.
	 *
	 * @return void
	 */
	public function setup(): void;

	/**
	 * Is run after the driver is initialized.
	 *
	 * @return void
	 */
	public function teardown(): void;

	/**
	 * Initialise the driver.
	 *
	 * @return void
	 */
	public function init(): void;

	/**
	 * Dispatch Event
	 *
	 * @param Event $event
	 * @return int|null
	 */
	public function dispatch( Event $event ): ?int;

	/**
	 * Cancel next occurrence of Event
	 *
	 * @param Event $event
	 */
	public function cancel_next( Event $event ): void;

	/**
	 * Cancel all occurrences of Event
	 *
	 * @param Event $event
	 */
	public function cancel_all( Event $event ): void;

	/**
	 * Get next occurrence of Event
	 *
	 * @param Event $event
	 * @return DateTimeImmutable|null
	 */
	public function find_next( Event $event ): ?DateTimeImmutable;

	/**
	 * Get all occurrences of Event
	 *
	 * @param Event $event
	 * @return DateTimeImmutable[]
	 */
	public function find_all( Event $event ): array;


	/**
	 * Checks if an event is scheduled.
	 *
	 * @param Event $event
	 * @return bool
	 */
	public function is_scheduled( Event $event ): bool;
}
