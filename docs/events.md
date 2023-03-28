# Event Types

There are 3 types of events which can be extended within your Application. These allow you to dispatch custom events to the [Event Queue](dispatch.md) to be handed by your Application.

These event types are:

* [Async](#async-event)
* [Delayed](#delayed-event)
* [Recurring](#recurring-event)

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
