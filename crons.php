<?php
require_once("config.php");
require_once("autoload.php");

$kernel = New \Kernel\Kernel();
$kernel->processCrons();

if ( count($kernel->messages) ){

	echo implode("<br/>", $kernel->messages);

}
?>