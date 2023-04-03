# Queue Driver

- [Queue Driver](#queue-driver)
  - [Action Scheduler](#action-scheduler)
  - [Custom Driver](#custom-driver)
    - [Using Custom Driver](#using-custom-driver)
    - [Cancelling Events](#cancelling-events)
    - [Finding Events](#finding-events)
    - [Checking if an Event is Scheduled](#checking-if-an-event-is-scheduled)

It is possible to use any queue system as the Driver. Out of the box, Perique comes with a `Action_Scheduler` driver, which uses the WooCommerce Action Scheduler library. This is the recommended driver for most use cases.

## Action Scheduler

When using the `Action_Scheduler` driver, setup is straightforward - just add the following before your Application is defined and booted:

```php
// file plugin.php
$factory = (new App_Factory(__DIR__))
   ->module( \PinkCrab\Queue\Module\Perique_Queue::class )
   ->default_setup()
   ->boot();
```

## Custom Driver

If you would like to create a custom driver, you can do so by implementing the `PinkCrab\Queue\Queue_Driver\Queue` interface.

```php
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
   public function dispatch( Event $event) : ?int;

   /**
    * Cancel next occurrence of Event
    *
    * @param Event $event
    */
   public function cancel_next( Event $event): void;

   /**
    * Cancel all occurrences of Event
    *
    * @param Event $event
    */
   public function cancel_all( Event $event): void;

   /**
    * Get next occurrence of Event
    *
    * @param Event $event
    * @return DateTimeImmutable|null
    */
   public function find_next( Event $event): ?DateTimeImmutable;

   /**
    * Get all occurrences of Event
    *
    * @param Event $event
    * @return DateTimeImmutable[]
    */
   public function find_all( Event $event): array;


   /**
    * Checks if an event is scheduled.
    *
    * @param Event $event
    * @return bool
    */
   public function is_scheduled( Event $event): bool;
}
```
### Using Custom Driver

To use a custom driver, you can pass an instance of the driver to the config callback when adding the Queue module.

```php
// file plugin.php
$factory = ( new App_Factory( __DIR__ ) )
   ->module( 
      Perique_Queue::class,
      function( Perique_Queue $queue ): Perique_Queue {
         $queue->set_queue_driver( new MyCustomDriver() );
         return $queue;
      }
   ->default_setup()
   ->boot();

As part of the `Bootstrap` process, the following are called on the driver:

### Driver Lifecycle

* `$this->driver->setup();` Is called as soon as the Bootstrap is initiated.
* `$this->driver->init();` Is called on -1 priority of the `init` hook, this ensures all Plugins are loaded.
* `$this->driver->teardown();` Is called on the `HOOKS::APP_INIT_POST_REGISTRATION` hook, which is triggered after the Application has been registered and all classes from the Registration Class list have been processed.

The remaining methods are for processing Events when they are passed to the driver.

> **PLEASE NOTE** It is possible to directly use the driver for processing events, but this is not recommended. Please see the [Queue Service and Queue Proxy Classes](queue-service.md) for more information on how to use the Queue Service.

### Dispatching Events

Events can be added to the queue by calling the `dispatch` method on the driver. This method takes an instance of `Event` and returns an `int` or `null` if the event was not dispatched.

```php
$result = $driver->dispatch( new MyEvent( array( 'foo' => 'bar' ) ) );
// $result = int|null
```

### Cancelling Events

It is possible to cancel either the next instance of an event, or all instances of an event. This is done by calling the `cancel_next` or `cancel_all` methods on the driver.

```php
$result = $driver->cancel_next( new MyEvent( array( 'foo' => 'bar' ) ) );
// $result = void
$result = $driver->cancel_all( new MyEvent( array( 'foo' => 'bar' ) ) );
// $result = void
```

### Finding Events

It is possible to both find the next occurrence of an event, or all occurrences of an event. This is done by calling the `find_next` or `find_all` methods on the driver.

```php
$result = $driver->find_next( new MyEvent( array( 'foo' => 'bar' ) ) );
// $result = DateTimeImmutable|null
$result = $driver->find_all( new MyEvent( array( 'foo' => 'bar' ) ) );
// $result = DateTimeImmutable[]
```

### Checking if an Event is Scheduled

It is possible to check if an event is scheduled by calling the `is_scheduled` method on the driver.

```php
$result = $driver->is_scheduled( new MyEvent( array( 'foo' => 'bar' ) ) );
// $result = bool
```
