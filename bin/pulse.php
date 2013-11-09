<?php
/**
 * Analyses the pull request and issues in a Github repository.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    http://opensource.org/licenses/MIT  MIT
 */

// Max out error reporting.
error_reporting(-1);
ini_set('display_errors', 1);

// Bootstrap the Joomla Framework.
require realpath(__DIR__ . '/../vendor/autoload.php');

try
{
	define('PULSE_CONFIG', realpath(__DIR__ . '/../etc/config.json'));

	$app = new Pulse\Application;
	$app->execute();
}
catch (Exception $e)
{
	// An exception has been caught, just echo the message.
	fwrite(STDOUT, $e->getMessage() . "\n");
	exit($e->getCode());
}
