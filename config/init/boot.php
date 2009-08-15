<?php
/*

   Main Boot File
    - find zynapse libs and set paths

*/

// set zynapse root path
if ( !defined("ZNAP_ROOT") ) {
	define("ZNAP_ROOT", dirname(dirname(dirname(__FILE__))));
}

// set zynapse config path
define("ZNAP_CONFIG", ZNAP_ROOT . "/config");


// include boot configuration
require_once(ZNAP_CONFIG . "/init/boot_config.php");


// find zynapse libs
if ( !empty($zynapse_libs) && is_file($zynapse_libs . "/zynapse.php") ) {
	define("ZNAP_LIB_ROOT", $zynapse_libs);
} elseif ( is_file(ZNAP_ROOT . "/vendor/zynapse/zynapse.php") ) {
	define("ZNAP_LIB_ROOT", ZNAP_ROOT . "/vendor/zynapse");
} elseif ( is_file(dirname(ZNAP_ROOT) . "/vendor/zynapse/zynapse.php") ) {
	define("ZNAP_LIB_ROOT", dirname(ZNAP_ROOT) . "/vendor/zynapse");
}



// require main zynapse class
require_once(ZNAP_LIB_ROOT . "/zynapse.php");


?>