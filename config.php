<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * List of enabled crons.
 * Value: classname
 */
define("CRONS", serialize(array(
	"SCH",
	"Coffee",
	"CoffeeSend"
)));


/**
  * List of hooks
  * Key: trigger word (lowercase)
  * Value: classname
  */
define("HOOKS", serialize(array(
	"roken?" => "Smoke"
)));

/**
 * posturl for webhooks
 * https://api.slack.com/incoming-webhooks
 */
define("POSTURL", "https://hooks.slack.com/services/example/example/example");

/**
 * Token of your slack application
 */
define("TOKEN", "example");

/**
 * Database settings
 */
define("DATABASE_HOST", "localhost");
define("DATABASE_USER", "username");
define("DATABASE_PASSWORD", "password");
define("DATABASE_NAME", "databasename");
?>
