![logo](/docs/Perique-Queue-Card.jpg "PinkCrab Perique Queue")

# Perique Queue
A queue abstraction for the PinkCrab Perique Plugin Framework. Comes with a built in Action Scheduler implementation, but can be extended to be run with anything.


[![Latest Stable Version](https://poser.pugx.org/pinkcrab/queue/v)](https://packagist.org/packages/pinkcrab/queue) [![Total Downloads](https://poser.pugx.org/pinkcrab/queue/downloads)](https://packagist.org/packages/pinkcrab/queue) [![Latest Unstable Version](https://poser.pugx.org/pinkcrab/queue/v/unstable)](https://packagist.org/packages/pinkcrab/queue) [![License](https://poser.pugx.org/pinkcrab/queue/license)](https://packagist.org/packages/pinkcrab/queue) [![PHP Version Require](https://poser.pugx.org/pinkcrab/queue/require/php)](https://packagist.org/packages/pinkcrab/queue)
![GitHub contributors](https://img.shields.io/github/contributors/Pink-Crab/Perique-Queue?label=Contributors)
![GitHub issues](https://img.shields.io/github/issues-raw/Pink-Crab/Perique-Queue)

[![WP 6.6 [PHP8.0-8.4] Tests](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_6_6.yaml/badge.svg)](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_6_6.yaml)
[![WP 6.7 [PHP8.0-8.4] Tests](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_6_7.yaml/badge.svg)](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_6_7.yaml)
[![WP 6.8 [PHP8.0-8.4] Tests](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_6_8.yaml/badge.svg)](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_6_8.yaml)
[![WP 6.9 [PHP8.0-8.4] Tests](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_6_9.yaml/badge.svg)](https://github.com/Pink-Crab/Perique-Queue/actions/workflows/WP_6_9.yaml)

[![codecov](https://codecov.io/gh/Pink-Crab/Perique-Queue/branch/master/graph/badge.svg?token=0sWrPDNZMt)](https://codecov.io/gh/Pink-Crab/Perique-Queue)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Pink-Crab/Perique-Queue/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Pink-Crab/Perique-Queue/?branch=master)

## Why?

I needed a queue abstraction for the PinkCrab Perique Plugin Framework, and I wanted to be able to use it with the Action Scheduler, but also with a custom queue implementation. So I created this.

## Installation

### Composer

```bash
composer require pinkcrab/queue
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

As of v2.2.0 Action Scheduler is pulled in as a composer dependency (`woocommerce/action-scheduler: 3.9.*`) rather than vendored into the repo. The module loads it from `vendor/woocommerce/action-scheduler/action-scheduler.php` by default. The path can still be overridden via the `pinkcrab_queue_action_scheduler_path` filter — useful if WooCommerce or another plugin has already loaded a copy and you want Perique Queue to reuse it.

> If the site has WooCommerce or any other plugin which includes the Action Scheduler, Action Scheduler's own internal version-selection picks the highest-version copy registered, so no changes are required on the consumer side.

For more details on setting up the module or creating custom drivers, please [see the Module Docs](docs/queue-driver.md) for more details.

### Events

To add an operation to the queue, a class which implements `PinkCrab\Queue\Event\Event` must be created. To make this process a little easier, we have 3 abstract classes which can be extended to create the event.

* [`PinkCrab\Queue\Event\Async_Event`](./docs/events.md#async-event) - A simple event which will be run as soon as the queue is processed.
* [`PinkCrab\Queue\Event\Delayed_Event`](./docs/events.md#delayed-event) - A simple event which will be run after a delay.
* [`PinkCrab\Queue\Event\Recurring_Event`](./docs/events.md#recurring-event) - A simple event which will be run after a delay, and then again after a delay.

Please [see the Events Docs](./docs/events.md) for more details.

### Dispatching Events and Interacting with the Queue


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

You can read more about the [`Queue_Service` here](./docs/queue-service.md).

### Event Listeners

As the Queue just trigger WordPress Actions, you can just use the standard WordPress Action hooks to listen for the events.

```php
add_action( 'my_event', function( $event ) {
    // Do something with the event.
}, 10, 1 );
```

But we have a custom listener which you can use if you want to create controller like classes. As with the general concept behind Perique, these are designed to be added to be added to the registration class list, and then constructed and processed via the Registration Process and the DI_Container.

To make use of this, you can easily extend from the `Abstract_Listener` class, and then add the class to the registration class list.

```php
<?php
use PinkCrab\Queue\Listener\Abstract_Listener;

class My_Listener extends Abstract_Listener {

    protected string $hook = 'my_event';

    public function handle( array $args ): void {
        // Do something with the event.
    }
}
```

For more details on the `Abstract_Listener` class, please [see the docs here](./docs/event-listener.md).

## Requires
* [PinkCrab Perique Framework V2.1 and above.](https://github.com/Pink-Crab/Perqiue-Framework)
* PHP 8.0+

## Tested Against

* PHP 8.0, 8.1, 8.2, 8.3 & 8.4
* WP 6.6, 6.7, 6.8 & 6.9
* MySQL 8.4

## License

### MIT License
http://www.opensource.org/licenses/mit-license.html  

## Previous Perique Support

* For support of all versions before Perique V2, please use version 1.0.* of this module.

## Change Log ##
* 2.2.0 - Drop PHP 7.x, require PHP 8.0+. Modernise the tooling chain (PHPStan 2.x at level max, PHPUnit 8|9, WPCS 3.x, phpunit-polyfills widened to include v4). Replace the WP 6.3/6.4/6.5/6.6 workflows with the WP 6.6–6.9 matrix (PHP 8.0–8.4, `mysql:8.4`) using `codecov/codecov-action@v4`. Suppress the WP 6.8 `wp_is_block_theme` early-call notice in `tests/wp-config.php`. Add `.scrutinizer.yml` + `tests/.env` from the canonical sources. **Action Scheduler switched from a vendored `lib/action-scheduler/` copy to a composer dependency (`woocommerce/action-scheduler: 3.9.*` → installs 3.9.3).** Default load path in `Action_Scheduler_Driver::setup()` now points at `vendor/woocommerce/action-scheduler/action-scheduler.php`; the `pinkcrab_queue_action_scheduler_path` filter still overrides for consumers that want to reuse WooCommerce's copy. `tests/bootstrap.php` updated to the new path. Drop the `Codeclimate Maintainability` badge from the README.
* 2.1.0 - Support for Perique V2.1.* and updated Action Scheduler to 3.9.1
* 2.0.3 - Updated Action Scheduler to 3.8.2
* 2.0.2 - Updated Action Scheduler to 3.7.1
* 2.0.1 - Dependency updates
* 2.0.0 - Updated to support Perique V2, added docs and updated underlying version of Action Scheduler.
* 1.0.0 - Tagged release, updates to pipelines and Dependencies for WP6.1
* 0.1.2 - Tweaks to DI Rule definitions
* 0.1.1 - Tweaks to dependencies
* 0.1.0 - Initial Release
