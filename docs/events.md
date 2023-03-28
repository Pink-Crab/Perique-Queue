# Event Types

There are 3 types of events which can be extended within your Application. These allow you to dispatch custom events to the [Event Queue](dispatch.md) to be handed by your Application.

These event types are:

- [Event Types](#event-types)
  - [Async Event](#async-event)
    - [Example](#example)
  - [Delayed Event](#delayed-event)
    - [Example](#example-1)
  - [Recurring Event](#recurring-event)
    - [Example](#example-2)

Each of these events comes with its own Abstract Class which must be extended to create your own custom event.

All events must have a matching [`Event_Listener`](event-listener.md) which will be triggered when the event is processed by the queue.

## Async Event

As the name implies, these events are triggered as soon as possible by the [Event Queue](dispatch.md). They are not delayed or recurring.

**Required Properties**
* `$hook` - The WP Hook/Action which will be triggered when the event is processed.
* `$group` - The hooks group, this is used to identify the plugin or theme which the event belongs to.
* `$data` - The data to be passed to the event.

**Optional Properties**
* `$is_unique` - This denotes if the event is unique, if set to true, the event will only be added to the queue if it does not already exist. This is useful for events which are triggered multiple times, but you only want to process once. Defaults to `false`.


### Example

```php
class MyEvent extends AsyncEvent {
   /** The event hook */
   protected string $hook = 'my_event';

   /** The queue group it belongs to */
   protected string $group = 'acme_plugin';

   /** The data to be passed to the event */
   protected ?array $data = array();

   /** Is the event unique */
   protected bool $is_unique = true;

   /** @param array $data */
   public function __construct( array $data = array() ) {
      $this->data = $data;
   }
}
```

This can then be dispatched to the [Event Queue](dispatch.md) by either injecting the `Queue_Service` into your Controller or by using the `Queue::dispatch()` helper function.

```php
class Some_Controller{
   private Queue_Service $queue;
   public function __construct( Queue_Service $queue ) {
      $this->queue = $queue;
   }

   public function some_method() {
      $this->queue->dispatch( new MyEvent( array( 'foo' => 'bar' ) ) );
   }
}

// Or

PinkCrab\Queue\Dispatch\Queue::dispatch( new MyEvent( array( 'foo' => 'bar' ) ) );
```

## Delayed Event

It is possible to defined an event which will not be triggered until a defined time. This is useful for events which need to be triggered at a later date, or after a certain amount of time.

> The delay is defined by a `DateTimeImmutable` object, the timezone of the existing site is added before the event is dispatched, so the delay will be relative to the site timezone.

**Required Properties**
* `$hook` - The WP Hook/Action which will be triggered when the event is processed.
* `$group` - The hooks group, this is used to identify the plugin or theme which the event belongs to.
* `$data` - The data to be passed to the event.

**Optional Properties**
* `$is_unique` - This denotes if the event is unique, if set to true, the event will only be added to the queue if it does not already exist. This is useful for events which are triggered multiple times, but you only want to process once. Defaults to `false`.

**Required Methods**
* `delayed_until()` - This method must return a `DateTimeImmutable` object which defines the delay for the event. *Not defining the delay will result in the event throwing an InvalidArgumentException.*

### Example

```php
class MyEvent extends DelayedEvent {
   /** The event hook */
   protected string $hook = 'my_event';

   /** The queue group it belongs to */
   protected string $group = 'acme_plugin';

   /** The data to be passed to the event */
   protected ?array $data = array();

   /** Is the event unique */
   protected bool $is_unique = true;

   /** @param array $data */
   public function __construct( array $data = array() ) {
      $this->data = $data;
   }

   /**
    * Returns the delay for the event.
    *
    * @return DateTimeImmutable
    */
   public function delayed_until(): DateTimeImmutable {
      return new DateTimeImmutable( '+1 day' );
   }
}
```

This event will be triggered 1 day after it is dispatched to the [Event Queue](dispatch.md).

> As with the [`AsyncEvent`](#async-event), this can then be dispatched to the [Event Queue](dispatch.md) by either injecting the `Queue_Service` into your Controller or by using the `Queue::dispatch()` helper function.

## Recurring Event

Recurring events are similar to the [Delayed Events](#delayed-event), but instead of being triggered once, they are triggered at a defined interval.

> The interval is defined by an integer of the number of seconds.

**Required Properties**
* `$hook` - The WP Hook/Action which will be triggered when the event is processed.
* `$group` - The hooks group, this is used to identify the plugin or theme which the event belongs to.
* `$data` - The data to be passed to the event.

**Optional Properties**
* `$is_unique` - This denotes if the event is unique, if set to true, the event will only be added to the queue if it does not already exist. This is useful for events which are triggered multiple times, but you only want to process once. Defaults to `false`.

**Required Methods**
* `interval()` - This method must return an integer of the number of seconds which defines the interval for the event. *Not defining the interval will result in the event throwing an InvalidArgumentException*

**Optional Methods**
* `delayed_until()` - This method can be used to define a delay for the event, if not defined or returns null, the event will be triggered immediately.

### Example

```php
class MyEvent extends RecurringEvent {
   /** The event hook */
   protected string $hook = 'my_event';

   /** The queue group it belongs to */
   protected string $group = 'acme_plugin';

   /** The data to be passed to the event */
   protected ?array $data = array();

   /** Is the event unique */
   protected bool $is_unique = true;

   /** @param array $data */
   public function __construct( array $data = array() ) {
      $this->data = $data;
   }

   /**
    * Returns the interval for the event.
    *
    * @return int
    */
   public function interval(): int {
      return 60 * 60 * 24; // 1 day
   }

   /**
    * Returns the delay for the event.
    *
    * @return DateTimeImmutable|null
    */
   public function delayed_until(): ?DateTimeImmutable {
      return new DateTimeImmutable( '+1 day' );
   }
}
```

The event will be triggered 1 day after it is dispatched to the [Event Queue](dispatch.md), and then every 1 day after that.

> As with the [`AsyncEvent`](#async-event), this can then be dispatched to the [Event Queue](dispatch.md) by either injecting the `Queue_Service` into your Controller or by using the `Queue::dispatch()` helper function.

