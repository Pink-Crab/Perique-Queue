# Queue Driver

It is possible to use any queue system as the Driver. Out of the box, Perique comes with a `Action_Scheduler` driver, which uses the WooCommerce Action Scheduler library. This is the recommended driver for most use cases.

## Action Scheduler

When using the `Action_Scheduler` driver, setup is pretty easy. Just add the following before you Application is defined and booted.

```php
// file plugin.php

// Register the Action Scheduler Driver.
$action_scheduler = Action_Scheduler_Driver::get_instance();

// Then init the Queue Bootstrap with the instance.
Queue_Bootstrap::init( $action_scheduler );
```

Once the Bootstrap is defined, its just a case of adding the `Queue_Middleware` to the Application.

```php
// file plugin.php
$factory = (new App_Factory(__DIR__))
   ->construct_registration_middleware( Queue_Middleware::class )
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
As part of the `Bootstrap` process, the following are called on the driver:

### Driver Lifecycle

* `$this->driver->setup();` Is called as soon as the Bootstrap is initiated.
* `$this->driver->init();` Is called on -1 priority of the `init` hook, this ensures all Plugins are loaded.
* `$this->driver->teardown();` Is called on the `HOOKS::APP_INIT_POST_REGISTRATION` hook, which is triggered after the Application has been registered and all classes form the Registration Class list has been processed.

The remaining methods are for processing Events when they are passed to the driver.

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
