<?php

/**
 * PHPUnit bootstrap file
 */

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Give access to tests_add_filter() function.
require_once getenv( 'WP_PHPUNIT__DIR' ) . '/includes/functions.php';

// Load all environment variables into $_ENV
try {
	$dotenv = Dotenv\Dotenv::createUnsafeImmutable( __DIR__ );
	$dotenv->load();
} catch (\Throwable $th) {
	// Do nothing if fails to find env as not used in pipeline.
}

define( 'FIXTURE_PATH', dirname( __DIR__ ) . '/tests/fixtures/' );

tests_add_filter(
	'muplugins_loaded',
	function() {
		// Include action scheduler to ensure tables are created
		require_once dirname( __DIR__ ) . '/lib/action-scheduler/action-scheduler.php';
	}
);
// Start up the WP testing environment.
require getenv( 'WP_PHPUNIT__DIR' ) . '/includes/bootstrap.php';