<?php

namespace Hook;

/**
  * Abstract Class Hook
  * @author Michel Heitbrink
  */
abstract class Hook{

	protected $kernel;
	protected $database;

	public function __construct($kernel){
		$this->kernel = $kernel;
		$this->database = $kernel->database;
	}

	abstract public function get($post);

}

?>
