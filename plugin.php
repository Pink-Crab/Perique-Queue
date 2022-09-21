<?php
require_once __DIR__ . '/vendor/autoload.php';

use PinkCrab\Queue\Queue_Bootstrap;
use PinkCrab\Queue\Queue_Driver\Queue;
use PinkCrab\Perique\Application\App_Factory;
use PinkCrab\Queue\Registration_Middleware\Queue_Middleware;
use PinkCrab\Queue\Queue_Driver\Action_Scheduler\Action_Scheduler_Driver;

$r = Action_Scheduler_Driver::get_instance();
Queue_Bootstrap::init( $r );


$app = ( new App_Factory( __DIR__ ) )->with_wp_dice( true )
	->di_rules( array() )
	->registration_classes( array() )
	->construct_registration_middleware( Queue_Middleware::class )
	->boot();

// add_action('init', function() use( $app ) {
// 	dump( $app );
// });


// Debug helpers, remove this in production.
add_filter(
	'wp_php_error_args',
	function( $message, $error ) {
		echo "<strong>Error type</strong> : {$error['type']}<hr>";
		echo "<strong>Message </strong> : <pre style='color: #333; font-face:monospace; font-size:8pt;'>{$error['message']}</pre><hr>";
		echo "<strong>File </strong> : {$error['file']}<hr>";
		echo "<strong>Line </strong> : {$error['line']}<hr>";
		dd( $error, $message );
	},
	2,
	10
);
