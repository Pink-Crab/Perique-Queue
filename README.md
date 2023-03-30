# Perique Queue
A queue abstraction for the PinkCrab Perique Plugin Framework. Comes with a built in Action Scheduler implementation, but can be extended to be run with anything.


[![Latest Stable Version](http://poser.pugx.org/pinkcrab/queue/v)](https://packagist.org/packages/pinkcrab/queue) [![Total Downloads](http://poser.pugx.org/pinkcrab/queue/downloads)](https://packagist.org/packages/pinkcrab/queue) [![Latest Unstable Version](http://poser.pugx.org/pinkcrab/queue/v/unstable)](https://packagist.org/packages/pinkcrab/queue) [![License](http://poser.pugx.org/pinkcrab/queue/license)](https://packagist.org/packages/pinkcrab/queue) [![PHP Version Require](http://poser.pugx.org/pinkcrab/queue/require/php)](https://packagist.org/packages/pinkcrab/queue)
![GitHub contributors](https://img.shields.io/github/contributors/Pink-Crab/Perique-Queue?label=Contributors)
![GitHub issues](https://img.shields.io/github/issues-raw/Pink-Crab/Perique-Queue)
[![WordPress 5.9 Test Suite [PHP7.2-8.1]](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_5_9.yaml/badge.svg)](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_5_9.yaml)
[![WordPress 6.0 Test Suite [PHP7.2-8.1]](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_6_0.yaml/badge.svg)](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_6_0.yaml)
[![WordPress 6.1 Test Suite [PHP7.2-8.1]](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_6_1.yaml/badge.svg)](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_6_1.yaml)
[![codecov](https://codecov.io/gh/Pink-Crab/Perique-Queue/branch/master/graph/badge.svg?token=0sWrPDNZMt)](https://codecov.io/gh/Pink-Crab/Perique-Queue)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Pink-Crab/Perique-Queue/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Pink-Crab/Perique-Queue/?branch=master)
[![Maintainability](https://api.codeclimate.com/v1/badges/6abaf4e934d80a1634b2/maintainability)](https://codeclimate.com/github/Pink-Crab/Perique-Queue/maintainability)


## Version
**Release 0.1.0**

## Why?

I needed a queue abstraction for the PinkCrab Perique Plugin Framework, and I wanted to be able to use it with the Action Scheduler, but also with a custom queue implementation. So I created this.

## Installation

### Composer

```bash
composer require pinkcrab/perique-queue
```

## Usage

### Setup Module

As with all Perique Modules, adding `Queue` to the Application is as simple as adding it to the `App_Factory`:

```php
$factory = (new App_Factory(__DIR__))
    ->module( \PinkCrab\Queue\Module\Perique_Queue::class )
    ->default_setup()
    ->boot();
```

#### Action Scheduler

Out of the box Perique Queue uses the Action Scheduler to run the queue. This is the recommended way to use the queue, as it is the most reliable and performant. Nothing further is required during setup to use the Action Scheduler. 

> If the site has WooCommerce or any other plugin which includes the Action Scheduler, this will be used instead of the Perique Queue version and no changes are required.

For more details on setting up the module or creating custom drivers, please [see the Module Docs](docs/queue-driver.md) for more details.

### Events

To add an operation to the queue, a class which implements `PinkCrab\Queue\Event\Event` must be created. To make this process a little easier, we have 3 abstract classes which can be extended to create the event.

* [`PinkCrab\Queue\Event\Async_Event`](./docs/events.md#async-event) - A simple event which will be run as soon as the queue is processed.
* [`PinkCrab\Queue\Event\Delayed_Event`](./docs/events.md#delayed-event) - A simple event which will be run after a delay.
* [`PinkCrab\Queue\Event\Recurring_Event`](./docs/events.md#recurring-event) - A simple event which will be run after a delay, and then again after a delay.

Please [see the Events Docs](./docs/events.md) for more details.

### Dispatching Events and Interacting with the Queue

There are 2 ways to interact with the queue. You can either inject the `Queue_Service` into your class, or use the `Queue` Facade/Proxy class. 

#### Queue Service

The `Queue_Service` is the main class for interacting with the queue. It can be injected into any class which is created via the DI_Container.

```php
use PinkCrab\Queue\Dispatch\Queue_Service;

class My_Class {

    public function __construct( Queue_Service $queue ) {
        $this->queue = $queue;
    }

    public function dispatch_event() {
        $this->queue->dispatch( new My_Event() );
    }

    public function get_next_event() {
        $event = $this->queue->find_next( new My_Event() );
    }

    public function cancel_next_event() {
        $this->queue->cancel_next( new My_Event() );
    }

    public function is_event_pending() {
        $pending = $this->queue->is_scheduled( new My_Event() );
    }
}
```
> By injecting the `Queue_Service` as a dependency, this will allow mocking the service much easier in tests.

#### Queue Proxy

You can access the proxy class at any time by using the `PinkCrab\Queue\Dispatch\Queue` class. This class is a proxy to the `Queue_Service` and will return the same instance of the service. 

```php
use PinkCrab\Queue\Dispatch\Queue;

// Dispatch an event
Queue::dispatch( new My_Event() );

// Get the next event
$event = Queue::find_next( new My_Event() );

// Cancel the next event
Queue::cancel_next( new My_Event() );

// Check if an event is pending in the queue
$pending = Queue::is_scheduled( new My_Event() );
```
> Please note using the Proxy class is discouraged, and you should inject the `Queue_Service` into your class.


For more details on the `Queue_Service` please [see the Queue Service Docs](./docs/queue-service.md).

