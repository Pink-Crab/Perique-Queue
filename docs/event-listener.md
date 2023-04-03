# Event Listener

As the Queue makes use of WordPress hooks (actions), it is necessary to define a listener for each event. The listener is responsible for triggering the event when it is processed by the queue.

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

## Create a custom listener

To create your own listener, you can implement the following interface:

```php
interface Listener {
   public function __invoke(): void;
   public function get_hook(): string;
   public function register( Hook_Loader $loader ): void;
}
```
The `register()` method will automatically be called and passed the `Hook_Loader` instance, this is used to register the listener with WordPress.

The `__invoke()` method will be called when the event is processed by the queue. In the `Abstract_Listener` class, the invoke method calls `func_get_args()` to get the data passed to the event, and then calls the `handle()` method, passing the data to it.

```php
public function __invoke(): void {
   $args = func_get_args();
   $this->handle( $args );
}
```
