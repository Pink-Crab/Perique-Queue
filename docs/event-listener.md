# Event Listener

As the Queue makes use of WordPress hooks(actions), it is necessary to define a listener for each event. The listener is responsible for triggering the event when it is processed by the queue.

Listeners are defined by extending the `Abstract_Listener` class, or by creating your own custom implementation of the `Listener` interface. All listeners that extend from `Abstract_Listener` can be added to the `Registration_List` to be processed by the regular Perique registration process.

## Example

```php
class MyListener extends Abstract_Listener {
   /** The event hook */
   protected string $hook = 'my_event';

   /** @param array $data */
   public function handle( array $data = array() ) {
      // Do something with the data
   }
}
```

> Add to registration list

```php
// file config/registration.php
return [
    .....,
    My_Listeners::class,
]
```

As the listeners are created via the DI_Container as part of the Registration Process, you can inject any dependencies you need into the constructor.

## Example

```php
class MyListener extends Abstract_Listener {
   /** The event hook */
   protected string $hook = 'my_event';

   /** @var MyService */
   protected MyService $service;

   /** @param MyService $service */
   public function __construct( MyService $service ) {
      $this->service = $service;
   }

   /** @param array $data */
   public function handle( array $data = array() ) {
      $this->service->do_something( $data );
   }
}
```

> As this makes use of WordPress Hooks, it is possible to have multiple listeners for the same event. This is useful if you want to split up your logic into multiple classes.