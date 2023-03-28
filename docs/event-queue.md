# Event Queue

To interact with the Event Queue, you have 2 options:

Use the `Queue` directly via the Queue Proxy.

```php
\PinkCrab\Queue\Dispatch\Queue::dispatch( new MyEvent() );
```
**_or_** 

By injecting the `Queue_Service` into any class (constructed via the Container).

```php
class Some_Controller{
   private Queue_Service $queue;
   public function __construct( Queue_Service $queue ) {
      $this->queue = $queue;
   }

   public function some_method() {
      $this->queue->dispatch( new MyEvent() );
   }
}
```