<?php

declare(strict_types=1);

/**
 * Event Interface
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

interface Event {

	/**
	 * Get the group for the event.
	 *
	 * @return string
	 */
	public function get_group(): string;

	/**
	 * Get the hook for the event.
	 *
	 * @return string
	 */
	public function get_hook(): string;

	/**
	 * Get the data for the event.
	 *
	 * @return mixed[]|null
	 */
	public function get_data(): ?array;

	/**
	 * Gets the delayed until date/time for the event.
	 *
	 * @return \DateTimeImmutable|null
	 */
	public function delayed_until(): ?DateTimeImmutable;

	/**
	 * Gets the interval between recurring events in seconds.
	 *
	 * @return int|null
	 */
	public function interval(): ?int;

	/**
	 * Gets if the event should be unique.
	 *
	 * @return bool
	 */
	public function is_unique(): bool;
}
