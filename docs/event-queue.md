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
> If you plan on writing tests for your code, you should avoid using the `Queue Proxy`, and instead inject the `Queue_Service` into your class. This allows for mocking of the `Queue_Service` in your tests.

## Methods

### dispatch

```php
public function dispatch( Event $event ): ?int
```

This will return the ID of the event if it was successfully dispatched, or `null` if it was not.

### cancel_next

```php
public function cancel_next( Event $event ): void
```

This will cancel the next occurrence of the event.

### cancel_all

```php
public function cancel_all( Event $event ): void
```

This will cancel all occurrences of the event.

### find_next

```php
public function find_next( Event $event ): ?DateTimeImmutable
```

This will return the next occurrence of the event, or `null` if there is no next occurrence.

### find_all

```php
public function find_all( Event $event ): array
```

This will return all occurrences of the event as an array of `DateTimeImmutable` instance, or an empty array if there are no occurrences.

## is_scheduled

```php
public function is_scheduled( Event $event ): bool
```

This will return `true` if the event is scheduled, or `false` if it is not.

> All of these methods are accessible via the `Queue` Proxy and the `Queue_Service`.