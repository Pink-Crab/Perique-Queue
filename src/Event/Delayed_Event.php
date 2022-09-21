<?php

declare(strict_types=1);

/**
 * Abstract class for Delayed_Event
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

namespace PinkCrab\Queue\Event;

use DateTimeImmutable;

abstract class Delayed_Event implements Event {

	/**
	 * The event name (Hook)
	 *
	 * @var string
	 */
	protected $hook;

	/**
	 * The queue group it belongs to
	 *
	 * @var string
	 */
	protected $group = '';

	/**
	 * The data to be passed to the event
	 *
	 * @var mixed[]|null
	 */
	protected $data = array();

	/**
	 * Denotes if the event is unique or not
	 *
	 * @var bool
	 */
	protected $is_unique = false;

	/**
	 * Get the group for the event.
	 *
	 * @return string
	 */
	final public function get_group(): string {
		return $this->group;
	}

	/**
	 * Get the hook for the event.
	 *
	 * @return string
	 */
	final public function get_hook(): string {
		return $this->hook;
	}

	/**
	 * Get the data for the event.
	 *
	 * @return mixed[]|null
	 */
	final public function get_data(): ?array {
		return $this->data;
	}

	/**
	 * Gets the delayed until date/time for the event.
	 *
	 * @return \DateTimeImmutable|null
	 */
	abstract public function delayed_until(): ?DateTimeImmutable;

	/**
	 * Gets the interval between recurring events.
	 *
	 * @return int|null
	 */
	final public function interval(): ?int {
		return null;
	}

	/**
	 * Is the event unique?
	 *
	 * @return bool
	 */
	final public function is_unique(): bool {
		return $this->is_unique;
	}
}
