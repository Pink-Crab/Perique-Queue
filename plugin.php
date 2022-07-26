<?php
require_once __DIR__ . '/vendor/autoload.php';

use PinkCrab\Perique\Application\App_Factory;


$app = ( new App_Factory( __DIR__ ) )->with_wp_dice( true )
	->di_rules( [] )
	->registration_classes( [])
	->boot();

dd( $app);