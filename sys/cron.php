<?php

namespace Cron;

/**
  * Abstract Class Cron
  * @author Michel Heitbrink
  */
abstract class Cron{

	protected $kernel;
	protected $database;

	public function __construct($kernel){
		$this->kernel = $kernel;
		$this->database = $kernel->database;
	}

	abstract public function run();

}

?>
