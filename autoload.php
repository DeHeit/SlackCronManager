<?php

/**
 * Function for loading classes
 * File must has filename like classname
 */
function findClass($dir, $name){

	if ( is_dir($dir) ){

		$files  = scandir($dir);

		foreach($files as $file){

			if($file == "." || $file == "..") continue;

			if( is_dir(rtrim($dir, "/") . "/" . $file) ){

				findClass(rtrim($dir, "/") . "/" . $file, $name);

			}
			else if( is_file(rtrim($dir, "/") . "/" . $file) ){

				if( $file == $name . ".php" || $file == strtolower($name) . ".php"){

					require_once(rtrim($dir, "/") . "/" . $file);

					return true;
				}
			}
		}
	}

	return false;
}

/**
  * Including files ending with inc.php
  */
function includeFiles($dir){

	if ( is_dir($dir) ){

		$files  = scandir($dir);

		foreach($files as $file){

			if($file == "." || $file == "..") continue;

			if( is_dir(rtrim($dir, "/") . "/" . $file) ){

				includeFiles(rtrim($dir, "/") . "/" . $file);

			}
			else if( is_file(rtrim($dir, "/") . "/" . $file) ){

				if( strpos($file, "inc.php") !== false ){

					require_once(rtrim($dir, "/") . "/" . $file);

					return true;
				}
			}
		}
	}

	return false;
}

/**
  * Autoloading clasess in sys folder and classes folder
  */
function __autoload($name) {

	if( strpos($name, '\\') !== false ){

		$name = explode("\\", $name);
		$name = $name[1];

	}

	$found = findClass( realpath(dirname(__FILE__)) . "/sys" , $name);

	if( !$found ){

		findClass( realpath(dirname(__FILE__)) . "/classes" , $name);

	}
}

/**
  * Automaticly load inc files in the folder includes
  */
includeFiles(realpath(dirname(__FILE__)) . "/includes");

?>